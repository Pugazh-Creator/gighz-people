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

        // Total compensation requests in the last 60 days
        $builder->select('COUNT(*) as total')
            ->where('start_date >=', date('Y-m-d', strtotime('-60 days')));

        $totalResult = $builder->get()->getRowArray();
        $total = $totalResult['total'] ?? 0;

        // Pending requests in the last 60 days
        $pending = $this->where('start_date >=', date('Y-m-d', strtotime('-60 days')))
            ->where('status', 'pending')
            ->countAllResults();

        return [
            'count'   => $total,
            'pending' => $pending
        ];
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
        $builder->where('YEAR(compensation_request.start_date)', date('Y'));
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
            'total' => $count->countAllResults()
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
        foreach ($querys as $row) {
            $total += $row['num_of_days'];
        }
        return $total;
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
