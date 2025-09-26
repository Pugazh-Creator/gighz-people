<?php

namespace App\Models;

use CodeIgniter\Database\Query;
use CodeIgniter\Model;

// this class is managing our leave_request Database 

class LeaveRquestModel extends Model
{
    protected $table            = 'leave_request';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    // this function for giving database all feild access
    public function __construct()
    {
        parent::__construct();
        $db = \Config\Database::connect();
        $this->allowedFields = $this->getAllColumns();
    }
    private function getAllColumns()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($this->table);
        return $fields;
    }




    public function getEmployee($emp_id)
    {
        $userModel = new UserModel();
        return $userModel->find($emp_id);
    }

    public function deleteLeave($id)
    {
        $this->delete($id);
    }

    //getting no of requst and pending with in the condition for showing in hr dashboard
    public function getAllLeave()
    {
        $builder = $this->db->table($this->table);

        // Total leave requests in the last 60 days
        $builder->select('COUNT(*) as total')
            ->where('start_date >=', date('Y-m-d', strtotime('-60 days')));

        $totalResult = $builder->get()->getRowArray();
        $total = $totalResult['total'] ?? 0;

        // Pending leave requests in the last 60 days
        $pending = $this->where('start_date >=', date('Y-m-d', strtotime('-60 days')))
            ->where('status', 'pending')
            ->countAllResults();

        return [
            'count'   => $total,
            'pending' => $pending
        ];
    }


    public function getAllLeaveRequests($search = null, $sortBy = 'id', $sortOrder = 'DESC', $limit = 8, $offset = 0) // this function is using for showing all employees leave requests in hr/leaveReaquest page
    {
        $builder = $this->db->table($this->table);
        $builder->select('leave_request.*,employees.name,employees.remaining_leaves');
        $builder->join('employees', 'leave_request.emp_id = employees.emp_id');
        // Filter: Dates between 10 days before and 24 days after today
        // $builder->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)');
        // $builder->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');

        // Apply search filter if any
        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('leave_request.status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('leave_request.leave_type', $search)
                ->orLike('leave_request.start_date', $search)
                ->orLike('leave_request.end_date', $search)
                ->groupEnd();
        }

        $builder->orderBy($sortBy, $sortOrder);

        // Set the limit and offset for pagination
        $builder->limit($limit, $offset);



        return $builder->get()->getResultArray();



        // return $this->select('leave_request.id, leave_request.emp_id,employees.designation,employees.department,employees.avator, employees.name,leave_request.reason, leave_request.leave_type, leave_request.start_date, leave_request.end_date, leave_request.status')
        //             ->join('employees', 'leave_request.emp_id = employees.emp_id')
        //             ->orderBy('leave_request.id', 'DESC')
        //             ->findAll();
    }






    //--------------------------------------------------------------------------------------------
    // Method to get the total count of leave requests (for pagination)
    public function getTotalLeaveRequests($search = null, $emp_id = null)
    {

        $builder = $this->db->table($this->table);
        $builder->select('leave_request.*, tbl_employee.name as name');
        $builder->join('employees', 'employees.emp_id = leave_request.emp_id');
        // Filter: Dates between 10 days before and 24 days after today
        // $builder->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)');
        // $builder->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
        if ($emp_id !== null) {
            $builder->where('employees.emp_id', $emp_id);
        }
        // Apply search filter if any
        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('leave_request.status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('leave_request.leave_type', $search)
                ->orLike('leave_request.start_date', $search)
                ->orLike('leave_request.end_date', $search)
                ->groupEnd();
        }

        return $builder->countAllResults(); // Get the total count of records
    }









    //---------------------------------------------------------------------------------------------
    public function getOurLeaveRequest($emp_id, $search = null, $sortBy = 'id', $sortOrder = 'DESC', $limit = 8, $offset = 0, $year = null) //this Function for showing datas in myLeaverequest page 
    {
        $builder = $this->db->table($this->table);
        $builder->select('leave_request.*, employees.name,employees.remaining_leaves');
        $builder->join('employees', 'leave_request.emp_id = employees.emp_id');
        // $builder->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)');
        // $builder->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
        $builder->where('employees.emp_id', $emp_id);

        // Apply search filter if any
        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('leave_request.status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('leave_request.leave_type', $search)
                ->orLike('leave_request.start_date', $search)
                ->orLike('leave_request.end_date', $search)
                ->groupEnd();
        }

        $builder->orderBy($sortBy, $sortOrder);

        // Set the limit and offset for pagination
        $builder->limit($limit, $offset);

        return   $builder->get()->getResultArray();
    }


    public function countEmployeeLeaves($emp_id)
    {
        return $this->where('emp_id', $emp_id)->countAllResults(); // Count rows matching the condition
    }


    public function getCompanyHalidays()
    {
        return $this->db->table('company_haliday')
            ->select('haliday_date')
            ->get()
            ->getResultArray();
    }


    // Get the current OE period (based on the current date)
    public function getCurrentOE()
    {
        $today = date('Y-m-d');
        $currentMonth = date('m', strtotime($today));
        $currentYear = date('Y', strtotime($today));

        $startDate = "$currentYear-$currentMonth-25";
        $end = "$currentYear-$currentMonth-24";
        $endDate = date('Y-m-d', strtotime("$end +1 month"));

        // Calculate the OE period based on the current date
        $period = ((int)date('m', strtotime($today)) - 1) / 1;
        return $period + 1;
    }

    public function joinEmployeesAndLeaveRequest($id)
    {
        $builder = $this->db->table($this->table);
        $builder->select('leave_request.*,employees.name,employees.official_mail,employees.emp_id')
            ->join('employees', 'leave_request.emp_id = employees.emp_id')
            ->where('id', $id);
        return $builder->get()->getRowArray();
    }

    public function getOEBasedLeave()
    {
        $year = date('Y');
        // $start = 
    }


    public function getLeavesAndCompensationsForOE($startDate, $endDate)
    {
        return $this->select('emp_id, start_date, end_date')
            ->where('start_date >=', $startDate)
            ->where('leave_date <=', $endDate)
            ->orWhere('compensation_date >=', $startDate)
            ->where('compensation_date <=', $endDate)
            ->groupBy('user_id')
            ->findAll();
    }

    public function getThisOELeaves($empID, $startDate, $endDate)
    {
        $query = $this->db->table($this->table)
            ->select('start_date, end_date, reason,total_num_leaves, emp_id, status')
            ->where('start_date >=', $startDate)
            ->where('end_date <= ', $endDate)
            ->where('emp_id', $empID)
            ->get()
            ->getResultArray();

        $leaveRequests = [];
        $leaveRequests['rejected'] = [];
        $leaveRequests['approved'] = [];

        foreach ($query as $row) {
            $start_date = $row['start_date'];
            $end_date = $row['end_date'];
            $status = $row['status'];
            if (strtolower($status) === 'approved') {
                $start = strtotime($start_date);
                $end = strtotime($end_date);

                while ($start <= $end) {
                    $leaveRequests['approved'][] = date('Y-m-d', $start);
                    $start = strtotime("+1 day", $start); // Corrected increment
                }
            } else if (strtolower($status) === 'rejected') {
                $start = strtotime($start_date);
                $end = strtotime($end_date);

                while ($start <= $end) {
                    $leaveRequests['rejected'][] = date('Y-m-d', $start);
                    $start = strtotime("+1 day", $start); // Corrected increment
                }
            } else if (strtolower($status) === 'pending') {
                $start = strtotime($start_date);
                $end = strtotime($end_date);

                while ($start <= $end) {
                    $leaveRequests['pending'][] = date('Y-m-d', $start);
                    $start = strtotime("+1 day", $start); // Corrected increment
                }
            }
        }
        return $leaveRequests;
    }
}
