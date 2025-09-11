<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

// this class is managing our employees database in mysql

class UserModel extends Model
{
    protected $table            = 'user'; //databse name 
    protected $primaryKey       = 'emp_id'; //primary key in our database
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


    //ALL ALL FIELD
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


    // joining a tables(relationship)
    public function getLeaveRequest($emp_id)
    {
        $leaveRequestModel = new LeaveRquestModel();
        return $leaveRequestModel->where('emp_id', $emp_id)->findAll();
    }

    // public function getAllEmployees()
    // {
    //     $builder = $this->db->table($this->table);
    //     $builder->select('leave_request.*,user.remaining_leaves, user.username');
    //     $builder->join('user', 'leave_request.staff_id = user.staff_id');
    // }


   
}
