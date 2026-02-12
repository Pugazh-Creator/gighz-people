<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRquestModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

class CompensationController extends BaseController
{
    public function __construct()
    {
        helper(['url', 'form']);
    }

    public function index()
    {
        return view('requiment/myCompensation');
    }
    
    
    // --------------------------------------------------------------------------------------------------------------------------   OLD


    // this function for showing employees leave requests
    public function getMycompensation()
    {
        //    try{

        // Get the current search and sort options from query parameters
        $search = $this->request->getGet('search');
        $sortBy = $this->request->getGet('sort_by') ?: 'id';
        $sortOrder = $this->request->getGet('sort_order') ?: 'desc';

        $emp_id = session()->get('emp_id');

        // Load the pagination library
        $pager = \Config\Services::pager();

        // Define the number of records per page
        $perPage = 8;

        // Get the current page number from the query string (default is 1)
        $page = $this->request->getGet('page') ?: 1;

        // Calculate the offset for the query
        $offset = ($page - 1) * $perPage;

        $compensationModel = new CompensationModel;

        $compens = $compensationModel->getOurCompenRequest($emp_id, $search, $sortBy, $sortOrder, $perPage, $offset);
        $total = $compens['total'];
        // Get the total number of pages
        $totalPages = ceil($total / $perPage);

        $employee = new EmployeeModel;
        $emp = $employee->find($emp_id);

        return view(
            'requirments/myCompensation',
            [
                'data' => $compens,
                'search' => $search,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'pager' => $pager,
                'totalRequests' => $total,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'leaveModel' => $compensationModel
            ]
        );
        //    }
        //    catch(Exception)
        //    {
        //         return redirect()->back();
        //    }
    }
}


