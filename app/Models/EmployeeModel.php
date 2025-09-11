<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table            = 'employees';
    protected $primaryKey       = 'emp_id';
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
    }private function getAllColumns()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($this->table);
        return $fields;
    }


    // it's for getting employee detaile base on employee id
    public function getEmoloyee($emp_id)
    {
        return $this->find($emp_id);
    }


    public function getUserRole($emp_id)
    {
        return $this->join('role', 'role.id = employees.role')
            ->where('emp_id', $emp_id)
            ->first();
    }

    // this function is used to get employee remaining leaves based on the employee ID 


    public function getRemainingLeaves($emp_id)
    {
        return $this->find('remaining_leaves')->where('emp_id', $emp_id);
    }


    // this function is used for manage the availabel leaves
    public function updateAvailableLeave()
    {
        $leaveRequestModel = new LeaveRquestModel();
        $oe_period = $leaveRequestModel->getCurrentOE();
        $leaveHistoryModel = new LeaveHistoryModel;
        $employeeModel = new EmployeeModel;


        // Get all records to update the available_leave

        $query = $employeeModel->find();

        foreach ($query as $row) {
            $available_leave = $row['remaining_leave'];

            if (date('m-d') == '12-24') {
                $year = date('Y');
                $data = [
                    'emp_id' => $row['emp_id'],
                    'balence' => $available_leave,
                    'year' => $year
                ];
                $leaveHistoryModel->save($data);
                $new_leave = 1;
                // echo 'new Year';
            }
            // Check if available_leave % 3 == 0, then add 2, else add 1
            elseif ($oe_period % 3 == 0) {
                $new_leave = $available_leave + 2;
                // echo '3rd month added 2 leave';
            } else {
                $new_leave = $available_leave + 1;
                // echo 'normal month added 1 leave';
            }

            // Update the available_leave value for each employee
            $employeeModel->update($row['emp_id'], ['remaining_leave' => $new_leave]);
        }
    }
}
