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
use Config\Database;
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
        $dashboard = new Dashboard;

        $data['basedata'] = $dashboard->baseDatas();
        $data['thisPage'] = 'HR Dashboard';

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('dashboard/hrdashboard', $data);
        echo view('templates/footer', $data);
    }

    public function getStaffDetails()
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

        // Pending count (last 60 days)
        $permissiondb = $db->query("
    SELECT COUNT(permission_status) AS count 
    FROM permission_hrs 
    WHERE permission_status = ? 
      AND permission_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
", ['pending'])->getResultArray();

        $permission_pending = $permissiondb[0]['count'] ?? 0;

        // Total count (last 60 days)
        $permissiondb = $db->query("
    SELECT COUNT(permission_status) AS count 
    FROM permission_hrs 
    WHERE permission_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
")->getResultArray();

        $permission_TOTAL = $permissiondb[0]['count'] ?? 0;

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
                            'dept' => $staff['dept'],
                            'records' => [],
                        ];
                    }

                    if (!isset($data[$empId]['records'][$oeKey])) {
                        $data[$empId]['records'][$oeKey] = [
                            'compensation' => 0,
                            'leaves' => 0
                        ];
                    }

                    $attendance = $db->query("SELECT COUNT(a.work_status) AS leave_total from attendance a 
                                                    JOIN employees e on e.attendance_id = a.user_id
                                                    WHERE (a.work_status = ? OR a.work_status = ? OR a.work_status = ?) AND 
                                                        e.emp_id = ? and a.date between ? and ?", ['APL', 'NA', 'RA', $empId, $startDate, $endDate])->getRowArray();

                    $data[$empId]['records'][$oeKey]['leaves'] = $attendance['leave_total'];

                    $data[$empId]['records'][$oeKey]['compensation'] = $compensationModel->getEmployeeCompensation($empId, $startDate, $endDate);

                    // Move to the next OE period
                    $start = strtotime("$endDate +1 day");
                }
            }
        }
        $datas = [
            'total' => $totals,
            'pending' => $pending,
            'totalCompen' => $totalCompen,
            'pendingCompen' => $pendingCompen,
            'data' => $data,
            'oe' => $oe,
            'per_pending' => $permission_pending,
            'per_total' => $permission_TOTAL
        ];

        return $this->response->setJSON($datas);
    }



    // ---------------------------------------------   LEAVE REQUESTS   ----------------------------------------- \\


    // CHANGE LEAVE REQUEST STATUS
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
        $reject_reason = '';

        // Extract data
        $start = $leaves['start_date'];
        $end = $leaves['end_date'];
        $reason = $leaves['reason'];
        $name = $leaves['name'];
        $leavetype = $leaves['leave_type'];
        $balLeave = $leaves['balence_leave'];
        $state = $leaves['status'];

        $employee = $userModel->find($emp_id);
        $empmail =  "jicol78930@hiepth.com"; ///$employee['official_mail'] ??

        // Deletion flow
        if ($status == 'delete') {
            if ($userModel->update($emp_id, ['remaining_leaves' => $leaveID['hold_balence_leave']])) {
                $leaveRequestModel->deleteLeave($id);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Leave Deleted Successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Failed to delete leave.']);
            }
        }

        if ($status == 'rejected') {
            $reject_reason = '<p><strong>Reject Reason:</strong> ' . $reason_data . '</p></br><p>Approved:' . $approved_leave . '</p><p>Rejected: ' . $rejectLeave . '</p>';
        }
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
            {$reject_reason}
            
            <p><strong>Best Regards</strong></p>
            <p>HR Team</p>";

            send_email($empmail, $subject, $message);

            if ($status == 'rejected') {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Leave Request Deleted Successfully.']);
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Leave request Approve successfully.']);
        } else {
            if ($status == 'rejected') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Reject Leave Request.']);
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Failed to Approve Leave Request.']);
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


    // SENDING LEAVE NOTIFICATIONS TO HR
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





    //PATH TO LEAVE REQUEST PAGE
    public function ShowingLeaveRequests()
    {
        $dashboard = new Dashboard;
        $data =  [
            'basedata' => $dashboard->baseDatas(),
            'thisPage' => 'Leave Requests',
        ];

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('applications/leaveRequests', $data);
        echo view('templates/footer', $data);
    }

    // GETTING ALL LEAVE REQUESTS
    public function getEmployeeLeaveRequests()
    {
        $db = db_connect();

        $data = $db->query("SELECT l.*, e.name, e.dept, e.remaining_leaves FROM leave_request l JOIN employees e ON e.emp_id = l.emp_id
                             WHERE l.created_at >= DATE_SUB(CURDATE(), INTERVAL 120 DAY) ")->getResultArray();
        return $this->response->setJSON($data);
    }

    //CALCULATE NO OF LEAVE DAYS
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

    // INCREASE LEAVE BASED ON OE
    public function updateEmployeeLeaves()
    {
        $employeeModel = new EmployeeModel;
        if ($employeeModel->updateAvailableLeave())
            return redirect()->back()->with('success', 'Leave Updated SuccessFully.');
        else {
            return redirect()->back()->with('fail', 'Leave Updated Fail.');
        }
    }

    // GETTING FIRST SATURDAYS 
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


    /** -----------------------------------PERMISSION REQUESTS ---------------------------------- */

    public function getallpermission()
    {
        $dashboard = new Dashboard;

        $data['basedata'] = $dashboard->baseDatas();
        $data['thisPage'] = "Permission Request";

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('applications/permission', $data);
        echo view('templates/footer', $data);
    }

    public function getPermission()
    {
        $db = db_connect();
        $data = $db->query("SELECT p.*,  e.name, e.dept, e.remaining_leaves FROM permission_hrs p
                    JOIN employees e ON e.emp_id = p.permission_user_id
                    WHERE p.permission_created >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)")->getResultArray();

        return $this->response->setJSON($data);
    }

    public function changepermissionstatus($id, $status, $userID)
    {

        $db = db_connect();
        $table = $db->table('permission_hrs');
        if ($status == 'delete') {

            $deleted = $table->where(['permission_id' => $id, 'permission_user_id' => $userID])->delete();
            if ($deleted) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Successfully Deleted']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Delete']);
            }
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
                    $employeeEmail = 'jicol78930@hiepth.com'; // Fetch from database
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


                return $this->response->setJSON(['status' => 'success', 'message' => "$status Successfully"]);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'message' => "Something went rong please try again."]);
    }


    /**
     *  ----------------------------------- COMPANY HOLIDAYS ----------------------------------
     */

    public function companyHoliday()
    {
        $dashboard = new Dashboard;
        $data['basedata'] = $dashboard->baseDatas();
        $data['thisPage'] = 'Company Holiday';

        $currentYear = date('Y');
        $data['currentYear'] = $currentYear;
        $data['year_selection'] = [];

        for ($year = 2020; $year <= $currentYear; $year++) {
            $data['year_selection'][] = $year;
        }

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('leave\companyHoliday', $data);
        echo view('templates/footer', $data);
    }

    public function getHolidays($year)
    {
        $db  = db_connect();

        $data = $db->query("SELECT * FROM company_holiday WHERE YEAR(holiday_date) = ? ", [$year])->getResultArray();

        return $this->response->setJSON($data);
    }

    public function getHolidayById($id)
    {
        $db  = db_connect();

        $data = $db->query("SELECT * FROM company_holiday WHERE id = ? ", [$id])->getRowArray();

        return $this->response->setJSON($data);
    }

    public function deleteHoliday($id)
    {
        $companyHoliday = new CompanyHolidayModel();

        try {
            if ($companyHoliday->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Holiday Deleted Successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Holiday Deletion Failed.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateHoliday($id)
    {
        $companyHoliday = new CompanyHolidayModel;

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
            return $this->response->setJSON(['status' => 'error', 'message' => 'Holiday Update Failed']);
        }
        $companyHoliday->update($id, $data);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Holiday Updated successfully']);
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
            return $this->response->setJSON(['status' => 'success', 'message' => 'Leave added Successfully.']);
        }
        return $this->response->setJSON(['status' => 'success', 'message' => 'Leave added failed.']);
    }




    /**
     * ------------------ PERMISSION ---------------------
     */

    public function updatePermission()
    {
        $hostdb = Database::Connect('hostinger');

        $db = db_connect();

        // Step 1: Clear destination table
        $db->table('tbl_user_permission')->truncate();

        // Step 2: Fetch data from source table
        $sourceData = $hostdb->table('tbl_user_permission')->get()->getResultArray();

        // Step 3: Insert into destination table
        if (!empty($sourceData)) {
            $db->table('tbl_user_permission')->insertBatch($sourceData);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data copied successfully from table1 to table2'
        ]);
    }

    /** ---------------------------- COMPENSATION ----------------------------------  */
    public function showAllCompensation()
    {
        $dashboard = new Dashboard;
        $data['basedata'] = $dashboard->baseDatas();
        $data['thisPage'] = "Compensation Requests";

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('applications/compensation', $data);
        echo view('templates/footer', $data);
    }

    public function getAllCompensationRequests()
    {
        $db = db_connect();

        $data = $db->query("SELECT c.*,  e.name, e.dept, e.remaining_leaves FROM compensation_request c
                    JOIN employees e ON e.emp_id = C.emp_id
                    WHERE C.created_at >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)")->getResultArray();
        return $this->response->setJSON($data);
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
                return $this->response->setJSON(['status' => 'success', 'message' => 'Compensation Deleted Successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete Compesation.']);
            }
        } else {
            // $leaves = $leaveRequestModel->find($id);


            $empmail = "jicol78930@hiepth.com";
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
                <p><strong>Reason:</strong> {$reason}</p>
                </br>
                <p>Best Regards</p>
                <p>HR Team</p>";

                send_email($employeeEmail, $subject, $message);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Request updated Successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Request updated Failed.']);
            }
        }
    }
}
