<?php

namespace App\Models;

use CodeIgniter\Email\Email;
use CodeIgniter\Model;
use Config\Database;

class CompensationModel extends Model
{
    protected $table            = 'compensation_request';
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

    public function __construct()
    {
        parent::__construct();
        $db =  Database::connect();
        $this->allowedFields = $this->getAllColumns();
    }
    private function getAllColumns()
    {
        $db = Database::connect();
        $feilds = $db->getFieldNames($this->table);
        return $feilds;
    }

    public function getAllCompensationrequest2()
    {
        $builder = $this->db->table($this->table);
        $builder->select('compensation_request.*')
        // Filter: Dates between 10 days before and 24 days after today
                ->where('YEAR(compensation_request.start_date)',date('Y'));

        $pending = $this->where('status','pending')->countAllResults();
        // $pending = $this->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)')
        //     ->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
        //     ->where('status','pending')->countAllResults();
        $data=[
            'count' => $builder->countAllResults(),
            'pending' => $pending
        ];
        
        return $data;
    }
    
    public function getAllCompensationrequest($search = null, $sortBy = 'id', $sortOrder = 'DESC', $limit = 8, $offset = 0) // this function is using for showing all employees leave requests in hr/leaveReaquest page
    {
        $builder = $this->db->table($this->table);
        $builder->select('compensation_request.*,employees.name');
        $builder->join('employees', 'compensation_request.emp_id = employees.emp_id');
        // $builder->where('YEAR(compensation_request.start_date)',date('Y'));

        // Filter: Dates between 10 days before and 24 days after today
        // $builder->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)');
        // $builder->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');

        // Apply search filter if any
        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('compensation_request.status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('compensation_request.start_date', $search)
                ->orLike('compensation_request.end_date', $search)
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
    public function getTotalCompen($search = null)
    {

        $builder = $this->db->table($this->table);
        $builder->select('compensation_request.*, tbl_employee.name as name');
        $builder->join('employees', 'employees.emp_id = compensation_request.emp_id');
        // Filter: Dates between 10 days before and 24 days after today
        // $builder->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)');
        // $builder->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');

        // Apply search filter if any
        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('compensation_request.status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('compensation_request.start_date', $search)
                ->orLike('compensation_request.end_date', $search)
                ->groupEnd();
        }

        return $builder->countAllResults(); // Get the total count of records
    }


    /**
     * gettin compensation base on empemployee ID
     */
    //---------------------------------------------------------------------------------------------
    public function getOurCompenRequest($emp_id, $search = null, $sortBy = 'id', $sortOrder = 'DESC', $limit = 8, $offset = 0) //this Function for showing datas in myLeaverequest page 
    {
        $builder = $this->db->table($this->table);
        $builder->select('compensation_request.*, employees.name,employees.remaining_leaves');
        $builder->join('employees', 'compensation_request.emp_id = employees.emp_id');
        $builder->where('YEAR(compensation_request.start_date)',date('Y'));
        // $builder->where('leave_request.start_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)');
        // $builder->where('leave_request.start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
        $count = $builder->where('employees.emp_id', $emp_id);




        // Apply search filter if any
        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('compensation_request.status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('compensation_request.start_date', $search)
                ->orLike('compensation_request.end_date', $search)
                ->groupEnd();
        }

        $builder->orderBy($sortBy, $sortOrder);
        // Set the limit and offset for pagination
        $builder->limit($limit, $offset);

        $data = [
            'result' => $builder->get()->getResultArray(),
            'total' =>$count->countAllResults()
        ];
        return $data;
    }

    public function getEmployeeCompensation($empId, $startDate, $endDate)
    {
        $querys = $this->db->table($this->table)
                    ->select('num_of_days')
                    ->where('emp_id', $empId)
                    ->where('start_date >= ', $startDate)
                    ->where('start_date <= ', $endDate)
                    ->where('status', 'approved')
                    ->get()->getResultArray();
                    $total = 0;
                    foreach($querys as $row)
                    {
                        $total += $row['num_of_days'];
                    }
        return $total;

        
        
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


