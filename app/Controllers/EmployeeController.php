<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveHistoryModel;
use App\Models\LeaveRquestModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use Exception;

class EmployeeController extends BaseController
{

    // enabeling feature
    public function __construct()
    {
        helper(['url', 'form']);
    }

    public function index() {}
    public function applyLeave()
    {
        $dashboard_controller = new Dashboard;
        $data['basedata'] = $dashboard_controller->baseDatas();
        $data['thisPage'] = "Application";

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('requirments/applyLeave', $data);
        echo view('templates/footer', $data);
    }

    private function isSameOEPeriod($startDate, $endDate)
    {
        // Get the OE period for both dates
        $oeStart1 = $this->getOEStartDate($startDate);
        $oeStart2 = $this->getOEStartDate($endDate);

        return $oeStart1 == $oeStart2;
    }

    private function getOEStartDate($date)
    {
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));

        // If day is before 25th, take the previous month's OE cycle
        if (date('d', strtotime($date)) < 25) {
            $month = $month - 1;
            if ($month == 0) {
                $month = 12;
                $year = $year - 1;
            }
        }

        return date('Y-m-25', strtotime("$year-$month-25")); // OE start date
    }

    public function leaveApplySubmit()
    {
        $session = \Config\Services::session();
        $emp_id = $session->get('emp_id');
        $name = $session->get('name');
        $leaveType = $this->request->getPost('leaveType');
        $startDate = $this->request->getPost('leave_start_date');
        $endDate = $this->request->getPost('leave_end_date');
        $reason = $this->request->getPost('leave_reason');

        $EmployeeModel = new EmployeeModel;
        $emp_data = $EmployeeModel->find($emp_id);
        $grade = $emp_data['grade'];
        $hrController = new HRController;
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $leaveDays = $hrController->calculateLeaveDays($start, $end);

        $data = [
            'emp_id' => $emp_id,
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $reason,
            'total_num_leaves' => $leaveDays,
            'balence_leave' => $emp_data['remaining_leaves'],
            'hold_balence_leave' => $emp_data['remaining_leaves']
        ];

        if (!$this->isSameOEPeriod($startDate, $endDate)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Leave dates must be within the same OE period.']);
        }

        if ($startDate <= $endDate) {
            $saveLeaveRequest = new LeaveRquestModel();
            $query = $saveLeaveRequest->insert($data);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Start date should be lesthen the End Date.']);
        }

        if (!$query) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to send Leave Request']);
        } else {
            // Send Email to HR
            $hrEmail = "jajapo2209@cerisun.com"; // Change to HR's email
            $subject = "New Leave Request Submitted from {$name}";
            $message = "<p>A new leave request has been submitted.</p>
                      <p><strong>Employee :</strong>{$name} - {$emp_id}</p>
                      <p><strong>Leave Type:</strong> {$leaveType}</p>
                      <p><strong>Dates:</strong> {$startDate} to {$endDate} Total days {$leaveDays}</p>
                      <p><strong>Reason:</strong> {$reason} </p>
                      <p>Please review and take action.</p>
                      <br>
                      <br>
                      <p>Best regards,</p>
                      <p>{$name}</p>
                      ";


            send_email($hrEmail, $subject, $message);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Leave Request Sent Successfully.']);
        }
    }







    // this function for showing employees leave requests
    public function getMyLaeves()
    {
        //    try{

        // Get the current search and sort options from query parameters
        $search = $this->request->getGet('search');
        $year = $this->request->getGet('year');
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

        $leaveRequestModel = new LeaveRquestModel();

        $leaves = $leaveRequestModel->getOurLeaveRequest($emp_id, $search, $sortBy, $sortOrder, $perPage, $offset);
        $total =  $leaveRequestModel->getTotalLeaveRequests($search, $emp_id);
        // Get the total number of pages
        $totalPages = ceil($total / $perPage);

        $hrController = new HRController;
        //calculate leaves eliminates company holidays and sundays
        foreach ($leaves as &$leave) {
            $leaveStart = new \DateTime($leave['start_date']);
            $leaveEnd = new \DateTime($leave['end_date']);
            $leaveDays = $hrController->calculateLeaveDays($leaveStart, $leaveEnd);
            $leave['num_leave_days'] = $leaveDays;
        }

        // showing availabel leaves in UI
        $totalLeave = 0;
        if ($year) {
            $leaveHistoryModel = new LeaveHistoryModel;
            $history = $leaveHistoryModel->where('emp_id', $emp_id)->where('year', $year)->first();
            $totalLeave = $history['balence'] ?? 0;
        } else {
            $employee = new EmployeeModel;
            $emp = $employee->find($emp_id);
            $totalLeave = $emp['remaining_leaves'];
        }


        return view(
            'requirments/empLeaves',
            [
                'data' => $leaves,
                'total_leaves' => $totalLeave,
                'search' => $search,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'pager' => $pager,
                'totalRequests' => $total,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'leaveModel' => $leaveRequestModel
            ]
        );
        //    }
        //    catch(Exception)
        //    {
        //         return redirect()->back();
        //    }
    }

    //  permission

    public function applypermission()
    {

        $session = session();
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');
        $reason = $this->request->getPost('reason');
        $emp_id = $session->get('emp_id');
        $name = $session->get('name');

        // return $this->response->setJSON($this->request->getPost());


        $db = db_connect();

        $query = $db->table("permission_hrs");

        $data = [
            'permission_user_id' => $emp_id,
            'permission_date' => $date,
            'permission_time' => $time,
            'permission_reason' => $reason,
        ];

        $status = $query->insert($data);
        if (!$status) {
            return $this->response->setJSON(['status' => 'error']);
        }

        // Send Email to HR
        $hrEmail = "ciwam22682@jeanssi.com"; // Change to HR's email
        $subject = "New Permission Request Submitted from {$name}";
        $message = "<p>A new Permission request has been submitted.</p>
                      <p><strong>Employee :</strong>{$name} - {$emp_id}</p>
                      <p><strong>Date:</strong> {$date}</p>
                      <p><strong>Time:</strong> {$time}</p>
                      <p><strong>Reason:</strong> {$reason} </p>
                      <p>Please review and take action.</p>
                      </br>
                      <p>Best regards</p>
                      <p>{$name}</p>";


        send_email($hrEmail, $subject, $message);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Permission Applied successfully']);
    }


    // fetch all permission 

    public function getmypermission()
    {

        $db = db_connect();
        $emp_id = session()->get('emp_id');
        $query = $db->query("SELECT * FROM permission_hrs WHERE permission_user_id = ? ORDER BY permission_created DESC", [$emp_id])->getResultArray();
        return $this->response->setJSON($query);
    }


    // -----------------------------------------------------------------------
    // NEW GET LEAVE REQUEST 29-08-2025

    public function getMyLeaves()
    {
        $emp_id = session()->get('emp_id');
        $db = db_connect();

        $data = $db->query("
        SELECT l.*, e.name 
        FROM leave_request l 
        JOIN employees e ON e.emp_id = l.emp_id 
        WHERE l.emp_id = ? 
        ORDER BY l.created_at ASC
    ", [$emp_id])->getResultArray();

        return $this->response->setJSON($data);
    }



    // ----------COMPENSATION---------------

    public function applyCompensation()
    {

        $compensationModel = new CompensationModel;
        $employeeModel = new EmployeeModel;

        $emp_id = session()->get('emp_id');
        $name = session()->get('name');
        $startdate = $this->request->getPost('compen_start_date');
        $enddate = $this->request->getPost('compen_end_date');
        $reason = $this->request->getPost('compen_reason');

        $start = new DateTime($startdate);
        $end = new DateTime($enddate);
        // getting no of days
        $intervel = $start->diff($end);
        $days = $intervel->days + 1;
        $bal_compen = $employeeModel->where('emp_id', $emp_id)->findAll();
        $compen = '';
        foreach ($bal_compen as $row) {
            $compen = $row['compensation'];
        }

        $data = [
            'emp_id' => $emp_id,
            'start_date' => $startdate,
            'end_date' => $enddate,
            'num_of_days' => $days,
            'reason' => $reason
        ];

        $employeeModel->set('compensation', $compen += $days)
            ->where('emp_id', $emp_id)
            ->update();
        if ($compensationModel->save($data)) {

            // Send Email to HR
            $hrEmail = "jajapo2209@cerisun.com"; // Change to HR's email
            $subject = "New Compensation Request Submitted from {$name}";
            $message = "<p>A new Compensation request has been submitted.</p>
                      <p><strong>Employee :</strong>{$name} - {$emp_id}</p>
                      <p><strong>Dates:</strong> {$startdate} to {$enddate} Total days {$days}</p>
                      <p><strong>Reason:</strong> {$reason} </p>
                      <p>Please review and take action.</p>
                      <br>
                      <br>
                      <p>Best regards,</p>
                      <p>{$name}</p>";

            send_email($hrEmail, $subject, $message);

            return $this->response->setJSON(['status' => 'success', 'message', 'Compensation Request Sumbited Successfully.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message', 'Failed to send Compensation.']);
        }
    }

    public function showMyCompensation()
    {

        $db = db_connect();
        $emp_id = session()->get('emp_id');
        $query = $db->query("SELECT * FROM compensation_request where emp_id = ? order by created_at desc", [$emp_id])->getResultArray();

        return $this->response->setJSON($query);
    }
}
