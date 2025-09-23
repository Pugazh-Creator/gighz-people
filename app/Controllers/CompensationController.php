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
    public function showAllCompensation()
    {
        // Get the current search and sort options from query parameters
        $search = $this->request->getGet('search');
        $sortBy = $this->request->getGet('sort_by') ?: 'id';
        $sortOrder = $this->request->getGet('sort_order') ?: 'desc';
        $role = session()->get('role');
        // Load the pagination library
        $pager = \Config\Services::pager();

        // Define the number of records per page
        $perPage = 8;

        // Get the current page number from the query string (default is 1)
        $page = $this->request->getGet('page') ?: 1;

        // Calculate the offset for the query
        $offset = ($page - 1) * $perPage;

        // Fetch the leave requests
        $compensationModel = new CompensationModel;
        $datas = $compensationModel->getAllCompensationrequest($search, $sortBy, $sortOrder, $perPage, $offset);

        // Get the total number of leave requests
        $totalRequests = $compensationModel->getTotalCompen($search);

        // Get the total number of pages
        $totalPages = ceil($totalRequests / $perPage);

        // Get the total number of leaves taken by each employee
        $employeeModel = new UserModel();
        $employeeData = $employeeModel->findAll();

        return view('leave/compensation', [
            'datas' => $datas,
            'employeeData' => $employeeData,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pager' => $pager,
            'totalRequests' => $totalRequests,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'role' => $role
        ]);
    }

    public function changeCompenStatus($id, $status, $emp_id, $noDays)
    {
        $compenModel = new CompensationModel;
        $employeeModel = new EmployeeModel;
        $emp = $employeeModel->find($emp_id);


        $compenID = $compenModel->find($id);

        $start = $compenID['start_date'];
        $end = $compenID['end_date'];
        $reason = $compenID['reason'];
        $name = $emp['name'];
        $state = $compenID['status'];
        // $empmail = $leaves['official_mail']

        if ($status == 'delete') {

            if ($compenModel->delete($id)) {
                return redirect()->back()->with('success', 'compensation Deleted Successfully');
            } else {
                return redirect()->back()->with('fail', 'Failed to compensation leave');
            }
        } else {
            // $leaves = $leaveRequestModel->find($id);


            $empmail = "pixewog885@sectorid.com";
            // $empmail = $emp['official_mail'];


            // Update the leave request status
            $datas = ['status' => $status];



            // $this->sendNotification("Employee", "Your leave request has been approved!");
            if ($compenModel->update($id, $datas)) {
                // Send Email to Employee
                $employeeEmail = $empmail; // Fetch from database
                $subject = "compensation Request {$status}";
                $message = "<p>{$name} Your compensation request has been <strong>{$status}</strong>.</p>
                <p><strong>Dates:</strong> {$start} to {$end} totaly {$noDays}</p>
                <p><strong>Reason:</strong> {$reason}</p>";

                send_email($employeeEmail, $subject, $message);

                return redirect()->back()->with('success', 'compensation request updated Successfully.');
            } else {
                return redirect()->back()->with('fail', 'Failed to update compensation Request.');
            }
        }
    }

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


