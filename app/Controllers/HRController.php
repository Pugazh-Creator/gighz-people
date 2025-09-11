<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\CompanyHolidayModel;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveHistoryModel;
use App\Models\LeaveRquestModel;
use App\Models\UserModel;
use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\HTTP\ResponseInterface;
use DateInterval;
use DatePeriod;
use DateTime;
use Stringable;

class HRController extends BaseController
{
    // enabeling feature
    public function __construct()
    {
        helper(['url', 'form']);
    }

    public function index()
    {
        $db = db_connect();
        $compensationModel = new CompensationModel;
        $compensation = $compensationModel->getAllCompensationrequest2();
        $totalCompen = $compensation['count'];
        $pendingCompen = $compensation['pending'];

        $leaveRequest = new LeaveRquestModel();
        $total = $leaveRequest->getAllLeave();
        $totals = $total['count'];
        $pending = $total['pending'];
        // $pending = $leaveRequest->where('status', 'pending')->countAllResults();

        $permissiondb = $db->query("SELECT COUNT(permission_status) as count FROM permission_hrs WHERE permission_status = ?",['pending'])->getResultArray();
        $permission_pending = $permissiondb[0]['count'];

        $permissiondb = $db->query("SELECT COUNT(permission_status) AS count FROM permission_hrs")->getResultArray();
        $permission_TOTAL = $permissiondb[0]['count'];

        $companyHolidayModel = new CompanyHolidayModel();
        $attendanceModel = new AttendanceModel();
        $EmployeeModel = new EmployeeModel();

        $today = date('Y-m-d');
        $year = date('Y', strtotime("$today - 1 year"));
        $month = date('d', strtotime($today)) >= 25 ? $month = date('m', strtotime($today)) : date('m', strtotime("$today - 1 month"));

        $currentYear = date('Y');

        $staffs = $EmployeeModel->findAll();
        $data = [];
        $oe = [];

        foreach ($staffs as $staff) {
            if ($staff['emp_status'] == '1') {
                $empId = $staff['emp_id'];
                $start = strtotime("$year-12-25"); // OE Start Date
                $end = strtotime("$currentYear-$month-24"); // OE End Date

                while ($start <= $end) {
                    $startDate = date('Y-m-d', $start);
                    $ensdate = date('Y-m', strtotime("$startDate +1 month"));
                    $endDate = "$ensdate-24";
                    $oeKey = date('M-y', strtotime($endDate));
                    $oe[$oeKey] = true;

                    if (!isset($data[$empId])) {
                        $data[$empId] = [
                            'name' => $staff['name'],
                            'records' => [],
                        ];
                    }

                    if (!isset($data[$empId]['records'][$oeKey])) {
                        $data[$empId]['records'][$oeKey] = [
                            'compensation' => 0,
                            'leaves' => 0
                        ];
                    }

                    // Get data for attendance and compensation
                    $attendance = $attendanceModel->getLeaves($empId, $startDate, $endDate);
                    $data[$empId]['records'][$oeKey]['compensation'] = $compensationModel->getEmployeeCompensation($empId, $startDate, $endDate);

                    // Sum up compensations
                    // foreach ($compensations as $compensation) {
                    //     $data[$empId]['records'][$oeKey]['compensation'] += $compensation['num_of_days'];
                    // }

                    // Count leaves excluding holidays and Sundays
                    foreach ($attendance as $attendand) {
                        $leaves = $attendand['date'];

                        if ($staff['leave_grade'] == 3) {
                            $holidayExists = $companyHolidayModel->where('holiday_date', $leaves)->first();
                        } else {
                            $holidayExists = $companyHolidayModel->where('holiday_date', $leaves)
                                ->whereIn('holiday_type', ['festival', 'first_saturday'])
                                ->first();
                        }

                        $isSunday = (date('w', strtotime($leaves)) == 0);
                        if (!$holidayExists && !$isSunday) {
                            $data[$empId]['records'][$oeKey]['leaves']++;
                        }
                    }

                    // Move to the next OE period
                    $start = strtotime("$endDate +1 day");
                }
            }
        }

        return view('dashboard/hr', [
            'total' => $totals,
            'pending' => $pending,
            'totalCompen' => $totalCompen,
            'pendingCompen' => $pendingCompen,
            'data' => $data,
            'oe' => $oe,
            'per_pending' => $permission_pending,
            'per_total' => $permission_TOTAL
        ]);
    }

    // -------------------   change Leave status   ---------------------------- \\

    public function change_status($id, $status, $emp_id, $noDays)
    {
        $today = date('Y-m-d');
        $leaveRequestModel = new LeaveRquestModel();
        $userModel = new EmployeeModel();

        $db = db_connect();

        $leaves = $leaveRequestModel->joinEmployeesAndLeaveRequest($id);
        $leaveID = $leaveRequestModel->find($id);

        $reason_data = $this->request->getPost('reason') ?? '';
        $approved_leave = $this->request->getPost('apc') ?? '';
        $rejectLeave = $this->request->getPost('rlc') ?? '';

        if (strtolower($status) == 'approved') {
            $approved_leave = $noDays;
            $rejectLeave = '0';
        }

        // Extract data
        $start = $leaves['start_date'];
        $end = $leaves['end_date'];
        $reason = $leaves['reason'];
        $name = $leaves['name'];
        $leavetype = $leaves['leave_type'];
        $balLeave = $leaves['balence_leave'];
        $state = $leaves['status'];

        if ($start < $today) {
            $db->query("SELECT * FROM tbl_lop WHERE lop_date between ? and  ?");
        }
        $employee = $userModel->find($emp_id);
        $empmail =  "seraki9689@pngzero.com"; ///$employee['official_mail'] ??

        // Deletion flow
        if ($status == 'delete') {
            if ($userModel->update($emp_id, ['remaining_leaves' => $leaveID['hold_balence_leave']])) {
                $leaveRequestModel->deleteLeave($id);
                return redirect()->back()->with('success', 'Leave Deleted Successfully');
            } else {
                return redirect()->back()->with('fail', 'Failed to delete leave');
            }
        }

        // // Approval logic: update balance if status is approved and current is pending
        // if ($state == 'pending' && $status == 'approved') {
        //     $newBalance = max(0, $employee['remaining_leaves'] - $noDays);
        //     $userModel->update($emp_id, ['remaining_leaves' => $newBalance]);
        // }

        // Update leave request
        $updateData = [
            'status' => $status,
            'leave_reject_reason' => $reason_data,
            'leave_approve_count' => $approved_leave,
            'leave_reject_count' => $rejectLeave,
            'leave_actual' => $approved_leave,
        ];

        if ($leaveRequestModel->set($updateData)->where('id', $id)->update()) {
            $subject = "Leave Request Update";
            $message = "<p>{$name}, your leave request has been <strong>{$status}</strong>.</p>
            <p><strong>Leave Type:</strong> {$leavetype}</p>
            <p><strong>Dates:</strong> {$start} to {$end}</p>
            <p><strong>Reason:</strong> {$reason}</p>
            
            <p><strong>Best Regards</strong></p>
            <p>HR Team</p>";

            send_email($empmail, $subject, $message);

            if ($status == 'rejected') {
                return $this->response->setJSON(['status' => 'success']);
            }

            return redirect()->to('leaveRequests2')->with('success', 'Leave request updated successfully.');
        } else {
            if ($status == 'rejected') {
                return $this->response->setJSON(['status' => 'error']);
            }
            return redirect()->to('leaveRequests2')->with('fail', 'Failed to update Leave Request.');
        }
    }


    // HR will edit the available leave (Remaining Leaves) 
    public function updateLeave()
    {
        $leaveRequest = new LeaveRquestModel();

        $leaveModel = new EmployeeModel;
        $id = $this->request->getPost('id');
        $availableLeaves = $this->request->getPost('available_leave');


        $leave = $leaveRequest->find($id);
        $employee = $leaveModel->find($leave['emp_id']);


        $leaveModel->update($leave['emp_id'], ['remaining_leaves' => $employee['remaining_leaves'] + $availableLeaves]);
        // $leaveRequest->update($id, ['balence_leave' => $leave['balence_leave'] + $availableLeaves]);

        return $this->response->setJSON(['responce' => 'success']);
    }


    // this function for sending notification to the HR Department page 
    public function check_new_leave_requests()
    {
        $leaveRequestModel = new LeaveRquestModel();
        $employee = new EmployeeModel;

        // Check if there are any leave requests that HR hasn't approved/rejected
        $newLeaveRequest = $leaveRequestModel->where('status', 'pending')->orderBy('id', 'desc')->first();
        $name = $employee->find($newLeaveRequest['emp_id']);

        if ($newLeaveRequest) {
            return $this->response->setJSON([
                'new_leave_request' => true,
                'name' => $name['name']
            ]);
        } else {
            return $this->response->setJSON(['new_leave_request' => false]);
        }
    }





    //this function for showing data and sorting,searching 
    public function ShowingLeaveRequests()
    {
        // Get the current search and sort options from query parameters
        $search = $this->request->getGet('search');
        $sortBy = $this->request->getGet('sort_by') ?: 'id';
        $sortOrder = $this->request->getGet('sort_order') ?: 'desc';

        // Load the pagination library
        $pager = \Config\Services::pager();

        // Define the number of records per page
        $perPage = 8;

        // Get the current page number from the query string (default is 1)
        $page = $this->request->getGet('page') ?: 1;

        // Calculate the offset for the query
        $offset = ($page - 1) * $perPage;

        // Fetch the leave requests
        $leaveRequestModel = new LeaveRquestModel();
        $leaveRequests = $leaveRequestModel->getAllLeaveRequests($search, $sortBy, $sortOrder, $perPage, $offset);

        // Get the total number of leave requests
        $totalRequests = $leaveRequestModel->getTotalLeaveRequests($search);

        // Get the total number of pages
        $totalPages = ceil($totalRequests / $perPage);



        //calculate leaves eliminates company holidays and sundays
        foreach ($leaveRequests as &$leave) {
            $leaveStart = new \DateTime($leave['start_date']);
            $leaveEnd = new \DateTime($leave['end_date']);
            $leaveDays = $this->calculateLeaveDays($leaveStart, $leaveEnd);
            $leave['num_leave_days'] = $leaveDays;
        }


        // Get the total number of leaves taken by each employee
        $employeeModel = new UserModel();
        $employeeData = $employeeModel->findAll();





        return view('dashboard/leaveRequests', [
            'leaveRequests' => $leaveRequests,
            'employeeData' => $employeeData,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pager' => $pager,
            'totalRequests' => $totalRequests,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    }

    //calculate no of leave days...
    public function calculateLeaveDays(\DateTime $start, \DateTime $end)
    {
        //fetch company holidays
        $companyHolidays = new CompanyHolidayModel();
        $holiday = $companyHolidays->findAll();
        $holidays = [];
        foreach ($holiday as $row) {
            $holidays[] = $row['holiday_date'];
        }

        $interval = $start->diff($end);
        $days = $interval->days + 1; // Including the start day
        $laeveDays = 0;
        for ($i = 0; $i < $days; $i++) {
            $currentDate = clone $start;
            $currentDate->modify("+$i day");

            if ($currentDate->format('N') != 7 && !in_array($currentDate->format('Y-m-d'), $holidays)) {
                $laeveDays++;
            }
        }
        return $laeveDays;
    }


    // public function leaverequest2()
    // {
    //     return view('dashboard/leaveRequests');
    // }

    public function updateEmployeeLeaves()
    {
        $employeeModel = new EmployeeModel;
        if ($employeeModel->updateAvailableLeave())
            return redirect()->back()->with('success', 'Leave Updated SuccessFully.');
        else {
            return redirect()->back()->with('fail', 'Leave Updated Fail.');
        }
    }


    public function companyHoliday()
    {
        $companyHolidays = new CompanyHolidayModel();
        $data['session'] = session()->get('role');
        // $holidays = $companyHolidays->getCompanyHalidays();
        $data['holidays'] = $companyHolidays->getHolidaysByType('festival');
        $data['saturday'] = $companyHolidays->getHolidaysByType('first_saturday');


        return view('leave\companyHoliday', $data);
    }

    public function addAndFetchHoliday()
    {
        $companyHolidays = new CompanyHolidayModel();

        $holidayName = $this->request->getPost('holiday_name');
        $holidayDate = $this->request->getPost('holiday_date');
        $holidayType = $this->request->getPost('holiday_type');

        $dateObject = new DateTime($holidayDate);
        $day = $dateObject->format('l');
        $month = $dateObject->format('F');

        // echo $day." ".$month;

        $data = [
            'holiday_date' => $holidayDate,
            'holiday_name' => $holidayName,
            'month' =>  $month,
            'day' => $day,
            'holiday_type' => $holidayType
        ];

        if ($companyHolidays->insert($data)) {
            return redirect()->back()->with('success', 'Leave added Successfully.');
        }
        return redirect()->back()->with('fail', 'Leave added failed.');
    }

    public function updateHoliday()
    {
        $companyHoliday = new CompanyHolidayModel;

        $id = $this->request->getPost('id');
        $holidayName = $this->request->getPost('holiday_name');
        $holidayDate = $this->request->getPost('holiday_date');

        $dateObject = new DateTime($holidayDate);
        $day = $dateObject->format('l');
        $month = $dateObject->format('F');

        $data = [
            'holiday_date' => $holidayDate,
            'holiday_name' => $holidayName,
            'month' =>  $month,
            'day' => $day
        ];

        if ($holidayName == '' || $holidayDate == '') {
            return redirect()->back()->with('fail', 'Holiday updated failed');
        }
        $companyHoliday->update($id, $data);
        return redirect()->back()->with('success', 'Updated successfully');
    }

    public function deleteHoliday($id)
    {
        $companyHoliday = new CompanyHolidayModel;
        if ($companyHoliday->delete($id)) {
            return redirect()->back()->with('success', 'Deleted successfully');
        }
        return redirect()->back()->with('fail', 'Deleted failed');
    }


    function getFirstSaturdays()
    {
        $firstSaturdays = [];
        $year = date('Y');
        $companyHoliday = new CompanyHolidayModel;


        // Loop through all 12 months
        for ($month = 1; $month <= 12; $month++) {
            // Get the first day of the month
            $firstDayOfMonth = strtotime("$year-$month-01");

            // Check if the first day of the month is a Saturday
            if (date('l', $firstDayOfMonth) == 'Saturday') {
                $firstSaturdays[] = date('Y-m-d', $firstDayOfMonth);
            } else {
                // Find the first Saturday of the month
                $daysToAdd = (6 - date('w', $firstDayOfMonth)) % 7;
                $firstSaturday = date('Y-m-d', strtotime("+$daysToAdd days", $firstDayOfMonth));
                $firstSaturdays[] = $firstSaturday;
            }
        }
        foreach ($firstSaturdays as $saturday) {
            $dateObject = new DateTime($saturday);
            $day = $dateObject->format('l');
            $months = $dateObject->format('F');
            $data = [
                'holiday_date' => $saturday,
                'holiday_name' => '1st Saturday',
                'month' => $months,
                'day' => $day,
                'holiday_type' => 'first_saturday'
            ];
            $companyHoliday->insert($data);
        }
        echo '<h1>updated Successfully</h1>';
    }


    /** Getting All Permission */

    public function getallpermission()
    {

        $db = db_connect();

        // $query = $db->query("SELECT * FROM permission_hrs ORDER BY permission_created DESC")->getResultArray();

        // Get the current search and sort options from query parameters
        $search = $this->request->getGet('search');
        $sortBy = $this->request->getGet('sort_by') ?: 'permission_id';
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

        $builder = $db->table('permission_hrs');
        $builder->select('permission_hrs.*,employees.name');
        $builder->join('employees', 'permission_hrs.permission_user_id = employees.emp_id');

        if ($search) {
            // $builder->like('employees.name', $search); // You can adjust this to search by leave type, status, etc.
            $builder->groupStart()
                ->like('employees.name', $search)
                ->orLike('permission_hrs.permission_status', $search)
                ->orLike('employees.emp_id', $search)
                ->orLike('permission_hrs.permission_date', $search)
                ->orLike('permission_hrs.permission_time', $search)
                ->orLike('permission_hrs.permission_reason', $search)
                ->groupEnd();
        }

        $builder->orderBy($sortBy, $sortOrder);

        // Set the limit and offset for pagination
        $builder->limit($perPage, $offset);
        $datas = $builder->get()->getResultArray();
        $totalRequests = $builder->countAll();

        // Get the total number of pages
        $totalPages = ceil($totalRequests / $perPage);

        // Get the total number of leaves taken by each employee
        $employeeModel = new UserModel();
        $employeeData = $employeeModel->findAll();

        // $data = [
        //     'datas' => $datas,
        //     'employeeData' => $employeeData,
        //     'search' => $search,
        //     'sortBy' => $sortBy,
        //     'sortOrder' => $sortOrder,
        //     'pager' => $pager,
        //     'totalRequests' => $totalRequests,
        //     'totalPages' => $totalPages,
        //     'currentPage' => $page,
        //     'role' => $role
        // ];

        // return $this->response->setJSON($data);


        return view('leave/permission', [
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

    public function changepermissionstatus($id, $status, $userID)
    {

        $db = db_connect();
        $table = $db->table('permission_hrs');
        if ($status == 'delete') {
            $table->where(['permission_id' => $id, 'permission_user_id' => $userID])->delete();
            return redirect()->back()->with('success', 'Permission hours deleted successfully.');
        } else if ($status != 'pending') {
            $updated = $table->set('permission_status', $status)->where(['permission_id' => $id, 'permission_user_id' => $userID])->update();
            if ($updated) {
                $details = $table->select('permission_hrs.*, employees.name, employees.official_mail')->join('employees', 'employees.emp_id =permission_hrs.permission_user_id')
                    ->where('permission_hrs.permission_id', $id)->get()->getResultArray();
                foreach ($details as $r) {
                    $name = $r['name'];
                    $date = $r['permission_date'];
                    $time = $r['permission_time'];
                    $reason = $r['permission_reason'];
                    $empmail = $r['official_mail'];
                }

                if (!empty($empmail)) {
                    $employeeEmail = $empmail; // Fetch from database
                    $subject = "Permission Request {$status}";
                    $message = "<p>Dear {$name}</p> 
                        </br>
                        <p>{$name} Your Permission request has been <strong>{$status}</strong>.</p>
                        <p><strong>Dates:</strong> {$date} </p>
                        <p><strong>Time:</strong> {$time} </p>
                        <p><strong>Reason:</strong> {$reason}</p>
                        </br>
                        <p>Best Regards</p>
                        <p>HR Team</p>";

                    send_email($employeeEmail, $subject, $message);
                }


                return redirect()->back()->with('success', 'Permission hours Status Updated successfully.');
            }
        }
        return redirect()->back()->with('fail', 'Something went rong please try again.');
    }


    /** 
     * REject reason POP-UP 
     */
}
