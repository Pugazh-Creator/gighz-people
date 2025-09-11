<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceLogModel;
use App\Models\AttendanceModel;
use App\Models\AttendanceUserModel;
use App\Models\CompanyHolidayModel;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRquestModel;
// use CodeIgniter\Database\Database;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use Config\Database;
use DateTimeZone;

class BiometricController extends BaseController
{

    /**
     * getting All calculated attendance Logs And showing in attendance logs page(UI)
     */
    public function index()
    {

        $model = new AttendanceModel;

        $selectedMonth = $this->request->getGet('month');
        $selectedYear = $this->request->getGet('year');

        // // Generate list of months for dropdown
        $data['months'] = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];
        // Generate year options (last 5 years + next 2 years)
        $currentYear = date('Y');
        $data['years'] = range($currentYear - 5, $currentYear + 2);

        // Set default selected values

        if (date('d') > 24) {
            $month = (date('m') == 12) ? 1 : date('m') + 1;
            $year = (date('m') == 12) ? date('Y') + 1 : date('Y');
        } else {
            $month = date('m');
            $year = date('Y');
        }

        $data['selectedMonth'] = $selectedMonth ?? $month;
        $data['selectedYear'] = $selectedYear ?? $year;

        $attendanceResult = $model->getMonthlyAttendance($selectedMonth, $selectedYear);


        $data['attendance'] = $attendanceResult['data'];
        $data['dates'] = $attendanceResult['dates'];
        // echo $attendanceResult['start']." <=> ".$attendanceResult['end'];
        return view('Attendance/attendanceLogs', $data);
        // return $this->response->setJSON($data);
    }



    /**
     * It will give all per day punches based on user id and dates
     */
    public function getPunchRecords()
    {
        $userId = $this->request->getGet('user_id');
        $date = $this->request->getGet('date');

        $model = new AttendanceLogModel;
        $punches = $model->getPunchLogs($userId, $date);

        return $this->response->setJSON($punches);
    }

    /**
     * This function is used to HR can add manually empllyee punches 
     */
    public function addManualPunch()
    {
        $userId = $this->request->getPost('user_id');
        $date = $this->request->getPost('date');
        $manualTime = $this->request->getPost('manual_time');

        $dateTime = $date . ' ' . $manualTime;  // Combining date and time

        // $data = [
        //     'employee_id' => $userId,
        //     'timestamp' => $dateTime,  // Storing full datetime
        // ];

        $data = [
            'employee_id' => $userId,
            'timestamp' => (new DateTime($dateTime))->format('Y-m-d H:i:s'),  // Convert to string
        ];

        $model = new AttendanceLogModel;
        $model->insert($data);

        // $model = new AttendanceLogModel;
        // Retrieve all punches for the given user & date
        $this->recalculatePunchTime($userId, $date);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * It's used for delete extra punches 
     */
    public function delete_punch()
    {
        $id = $this->request->getPost('id');

        $attendanceLogModel = new AttendanceLogModel;
        $data = $attendanceLogModel->select('employee_id, DATE(timestamp) as date')->where('id', $id)->first();
        $attendanceLogModel->where('id', $id)->delete();

        $userId = $data['employee_id'];
        $date =  $data['date'];
        $this->recalculatePunchTime($userId, $date);


        return json_encode(['status' => 'success']);
    }
    /**
     * its for if HR can make any changes that delete or Update that employee punches again will calculate 
     */
    public function recalculatePunchTime($userId, $date, $type = null) //
    {

        // Fetch user details
        $attendanceUser = new AttendanceUserModel();
        $model = new AttendanceLogModel();
        $punches = $model->getPunchLogs($userId, $date);

        $attUser = $attendanceUser->where('user_id', $userId)->first();
        $empAttendanceType = $attUser['emp_attendance_type'];

        if ($type != null) {
            $empAttendanceType = $type;
        }

        $worked = $empAttendanceType == 2 ? 'Feildtech' : ($empAttendanceType == 3 ? 'General' : ($empAttendanceType == 4 ? 'Watchman' : ($empAttendanceType == 1 ? 'Normal' : 'NWM'))
        );

        $times = array_column($punches, 'time'); // Extract only the times
        $totalWorkTime = 0;
        $newTime = "00:00";
        $work_status = 'Absent';
        if (count($times) % 2 == 1) {
            $newTime = 'Error';
        } else {

            if ($empAttendanceType == 1) { // Multiple punch logs
                for ($i = 0; $i < count($times) - 1; $i += 2) {

                    $work_status = 'Present';

                    $in = strtotime($times[$i]);
                    $out = strtotime($times[$i + 1]);
                    $totalWorkTime += ($out - $in)  / 3600; // Accumulate total time in seconds
                }
            } else if ($empAttendanceType == 2) { // Single in-out punch
                $feildIn = strtotime($times[0]);
                $feildOut = strtotime($times[count($times) - 1]);
                $totalTime = ($feildOut - $feildIn) / 3600; // Convert seconds to hours
                $work_status = 'Present';
                // Apply deductions
                if ($totalTime < 2) {
                    $totalWorkTime = $totalTime;
                } elseif ($totalTime < 5) {
                    $work_status = 'Offday';
                    $totalWorkTime = $totalTime - 0.167; // Deduct 10 minutes
                } elseif ($totalTime < 6.67) {
                    $work_status = 'Permission';
                    $totalWorkTime = $totalTime - 0.833; // Deduct 50 minutes
                } else {
                    $totalWorkTime = $totalTime - 1; // Deduct 1 hour
                }
            } else if ($empAttendanceType == 3 || $empAttendanceType == 4) { // Single in-out punch
                $feildIn = strtotime($times[0]);
                $feildOut = strtotime($times[count($times) - 1]);
                $totalTime = ($feildOut - $feildIn) / 3600; // Convert seconds to hours
                $totalWorkTime = $totalTime; // Deduct 1 hour
                $work_status = 'Present';

                if ($totalTime < 5) {
                    $work_status = 'Offday';
                } elseif ($totalTime < 6.67) {
                    $work_status = 'Permission';
                }
            }

            // Convert total seconds for `empAttendanceType 1` to HH:MM
            $hours = floor($totalWorkTime);
            $minutes = round(($totalWorkTime - $hours) * 60);
            $newTime = sprintf('%02d:%02d', $hours, $minutes);
        }




        // Store in database
        $attendanceModel = new AttendanceModel();
        $attendanceModel->where(['user_id' => $userId, 'date' => $date])
            ->set(['total_hours' => $newTime, 'work_type' => $worked, 'work_status' => $work_status])
            ->update();
    }

    public function chanegTypeOfWork()
    {

        $attendanceModel = new AttendanceModel;
        // $bioType = '5';
        // $userId = '17';
        // $date = '2025-03-21';
        $bioType = $this->request->getPost('bioType');
        $userId = $this->request->getPost('userId');
        $date = $this->request->getPost('date');

        if ($bioType == '1') {
            $this->recalculatePunchTime($userId, $date, 1);
            $attendanceModel->where('user_id', $userId)
                ->where('DATE(date)', $date) // Correcting the date condition
                ->set('work_status', 'Present')   // Use set() for updating
                ->update();
            return json_encode(['status' => 'success']);
        }
        if ($bioType == '2') {
            $this->recalculatePunchTime($userId, $date, 2);
            $attendanceModel->where('user_id', $userId)
                ->where('DATE(date)', $date) // Correcting the date condition
                ->set('work_status', 'Present')   // Use set() for updating
                ->update();
            return json_encode(['status' => 'success']);
        }

        if ($bioType == '6') {
            $attendanceModel->where('user_id', $userId)
                ->where('DATE(date)', $date) // Correcting the date condition
                ->set('work_status', 'OD')   // Use set() for updating
                ->update();
            return json_encode(['status' => 'success']);
        }
        if ($bioType == '7') {
            $attendanceModel->where('user_id', $userId)
                ->where('DATE(date)', $date) // Correcting the date condition
                ->set('work_status', 'WFH')   // Use set() for updating
                ->update();
            return json_encode(['status' => 'success']);
        }

        return json_encode(['status' => 'error']);
    }


    /** Showing Employees Leaves to the HR */
    public function sample()
    {
        $companyHolidayModel = new CompanyHolidayModel();
        $compensationModel = new CompensationModel();
        $attendanceModel = new AttendanceModel();
        $EmployeeModel = new EmployeeModel();

        $today = date('Y-m-d');
        $year = date('Y', strtotime("$today - 1 year"));
        $month = date('m', strtotime("$today - 1 month"));
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
                    $compensations = $compensationModel->getEmployeeCompensation($empId, $startDate, $endDate);

                    // Sum up compensations
                    foreach ($compensations as $compensation) {
                        $data[$empId]['records'][$oeKey]['compensation'] += $compensation['num_of_days'];
                    }

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

        return view('leave/staffleaves', ['data' => $data, 'oe' => $oe]);
    }


    /** Upload Biometric Data */
    public function syncToHostinger()
    {
        // Get unsynced attendance records
        $attendanceModel = new AttendanceModel;

        $startDate = $this->request->getPost('start');
        $endDate = $this->request->getPost('end');

        // $startDate ='2025-04-25';
        // $endDate = '2025-04-27';


        $attendanceData = $attendanceModel
            ->select('attendance.*, attendance_users.emp_id, attendance_users.name')
            ->join('attendance_users', 'attendance_users.user_id = attendance.user_id')
            ->where('attendance_users.emp_attendance_type !=', '4')
            ->where('attendance_users.emp_attendance_type !=', '3')
            ->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->where('attendance_users.emp_id !=', '')
            ->findAll();

        $error = $attendanceModel
            ->join('attendance_users', 'attendance_users.user_id = attendance.user_id')
            ->where('total_hours', 'Error')
            ->where('attendance_users.emp_id !=', '')
            ->where('attendance_users.employee_status', '1')
            ->where('attendance_users.emp_attendance_type !=', '4')
            ->where('attendance_users.emp_attendance_type !=', '3')
            ->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->findAll();

        if (!empty($error)) {
            return $this->response->setJSON(['status' => 'error']);
        }

        // Connect to remote Hostinger DB
        $remoteDB = Database::connect('hostinger');

        try {
            $hostinger = $remoteDB->table('tbl_attendance_workhour');
            // $onlineTimesheet = $hostinger->where('attendance_date >=', $startDate)
            //     ->where('attendance_date <=', $endDate)->get()->getResultArray();

            // $exsist = [];
            // foreach ($onlineTimesheet as $row) {
            //     $exsist[] = $row['attendance_emp_id'] . ":" . $row['attendance_date'];
            // }

            foreach ($attendanceData as $row) {
                $emp_id = $row['emp_id'];
                $date = $row['date'];
                $attendance_type = $row['work_status'] === 'Present' ? 'P' : $row['work_status'];
                $data =
                    [
                        'attendance_emp_name' => $row['name'],
                        'attendance_emp_id' => $emp_id,
                        'attendance_date' => $date,
                        'attendance_type' => $attendance_type,
                        'attendance_working_hours' => $row['total_hours']
                    ];
                $exist = $remoteDB->query("SELECT * FROM tbl_attendance_workhour WHERE attendance_date = ? AND attendance_emp_id = ?", [$date, $emp_id])->getResultArray();
                if ($exist) {
                    $hostinger->set($data)->where('attendance_date', $date)->where('attendance_emp_id', $emp_id)->update();
                } else {
                    $hostinger->insert($data);
                }
            }

            return $this->response->setJSON(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'fail', 'error' => $e->getMessage()]);
        }
    }

    public function refreachBimetric()
    {
        $start = $this->request->getPost('start');
        $end = $this->request->getPost('end');

        $fetchlogs = $this->fetchLogs($start, $end);
        $calculation = $this->calculateEmployeeHours($start, $end);

        if ($fetchlogs && $calculation) {
            return $this->response->setJSON(['response' => 'success']);
        }
        return $this->response->setJSON(['response' => 'error']);
    }


    public function fetchLogs($start, $end)
    {


        $attendanceModelLog = new AttendanceLogModel;

        $records = $attendanceModelLog
            ->select('id')
            ->where("DATE(timestamp) >=", $start)
            ->where("DATE(timestamp) <=", $end)
            ->findAll();

        $ids = array_column($records, 'id');

        if (!empty($ids)) {
            $attendanceModelLog->whereIn('id', $ids)->delete();
        }

        try {
            // Step 1: Execute Node.js script
            $output = shell_exec('node C:/xampp/htdocs/gighz/public/asset/js/biometric.js 2>&1');

            // Step 2: Extract the JSON object from output
            preg_match('/\{.*\}/s', $output, $matches); // capture the JSON part only

            if (!isset($matches[0])) {
                throw new \Exception('Invalid response from biometric script');
            }

            $logsData = json_decode($matches[0], true);

            if (!$logsData || !isset($logsData['data'])) {
                throw new \Exception('Biometric device is not connected or returned invalid data');
            }

            $logs = $logsData['data'];

            // Step 3: Filter and group logs by date
            foreach ($logs as $log) {
                $timestampUtc = $log['recordTime'] ?? null;
                if (!$timestampUtc) continue;

                // Convert UTC timestamp to Asia/Kolkata timezone
                $dateTime = new DateTime($timestampUtc, new DateTimeZone('UTC'));
                $dateTime->setTimezone(new DateTimeZone('Asia/Kolkata'));
                $localTimestamp = $dateTime->format('Y-m-d H:i:s');
                $date = $dateTime->format('Y-m-d');

                if ($date >= $start && $date <= $end) {
                    $data = [
                        'employee_id' => $log['deviceUserId'],
                        'timestamp' => $localTimestamp,  // Store converted timestamp
                        'status' => '0'
                    ];
                    $attendanceModelLog->insert($data);
                }
            }
            // Step 4: Return response
            return true;
            // return $this->response->setJSON(['status' => 'success']);
        } catch (\Exception $e) {
            // return 'error : '.$e;
            return $this->response->setJSON(['status' => 'error', 'err' => $e]);
        }
    }

    /**-
     * ---------------------------------- calculate employee Hourse ------------------------
     */

    public function calculateEmployeeHours($start, $end)
    {
        $db = \Config\Database::connect();

        $db->query("delete from attendance where id in (select id from( select id from attendance
        where date between ? and ?) as temp )", [$start, $end]);


        $attendanceUsers = $db->query("SELECT * FROM attendance_users")->getResultArray();
        $holidaysQuery = $db->query("SELECT holiday_date FROM company_holiday")->getResultArray();

        $holidays = array_map(function ($row) {
            return date('Y-m-d', strtotime($row['holiday_date']));
        }, $holidaysQuery);

        // $defaultStartDate = '2024-12-24';
        $count = 0;
        foreach ($attendanceUsers as $user) {
            $user_id = $user['user_id'];
            $emp_id = $user['emp_id'];
            $name = $user['name'];
            $status = $user['employee_status'];
            $type = $user['emp_attendance_type']; // 1 = normal, 2 = field


            if ($status == '1' && ($emp_id != '' || $type == 3 || $type == 4)) {



                // Get department (optional)
                $empData = $db->query("SELECT dept FROM employees WHERE emp_id = ?", [$emp_id])->getRowArray();

                $startDate = $start;
                $endDate = $end;

                if (!empty($emp_id)) {
                    $leaveData = $this->getThisOELeaves($db, $emp_id, $startDate, $endDate); // You must define this method
                }

                while ($startDate <= $endDate) {

                    $logs = $db->query("SELECT timestamp FROM attendance_logs WHERE employee_id = ? AND DATE(timestamp) = ? ORDER BY TIME(timestamp)", [$user_id, $startDate])->getResultArray();

                    $totalHours = "00:00";
                    $workType = "Absent";

                    $worked = $type == 2 ? 'Feildtech' : ($type == 3 ? 'General' : ($type == 4 ? 'Watchman' : 'Normal')
                    );

                    if (count($logs) % 2 == 1) {
                        $totalHours = "Error";
                        $workType = "Present";
                    } elseif (count($logs) > 0) {
                        $workType = "Present";
                        $totalWorkTime = 0;

                        if ($type == 1) { // normal
                            for ($i = 0; $i < count($logs) - 1; $i += 2) {
                                $in = strtotime($logs[$i]['timestamp']);
                                $out = strtotime($logs[$i + 1]['timestamp']);
                                $totalWorkTime += ($out - $in) / 3600;
                            }

                            if ($totalWorkTime < 5) {
                                $workType = 'Offday';
                            } else if ($totalWorkTime < 7) {
                                $workType = 'Permission';
                            } else {
                                $workType = 'Present';
                            }
                        } else if ($type == 2) { // field staff
                            $in = strtotime($logs[0]['timestamp']);
                            $out = strtotime(end($logs)['timestamp']);
                            $hours = ($out - $in) / 3600;

                            if ($hours < 2) $totalWorkTime = $hours;
                            elseif ($hours < 5) $totalWorkTime = $hours - 0.167;
                            elseif ($hours < 6.67) $totalWorkTime = $hours - 0.833;
                            else $totalWorkTime = $hours - 1;
                        } else if ($type == 3 || $type == 4) {
                            $in = strtotime($logs[0]['timestamp']);
                            $out = strtotime(end($logs)['timestamp']);
                            $hours = ($out - $in) / 3600;

                            $totalWorkTime = $hours;
                        }

                        $h = floor($totalWorkTime);
                        $m = round(($totalWorkTime - $h) * 60);
                        $totalHours = sprintf('%02d:%02d', $h, $m);
                    }

                    if ($workType === 'Absent' && $type != 3 && $type != 4) {
                        $isSunday = date('w', strtotime($startDate)) == 0;
                        if ($isSunday || in_array($startDate, $holidays)) {
                            $workType = 'Absent';
                        } else {
                            if (in_array($startDate, $leaveData['approved'])) {
                                $workType = 'APL';
                            } elseif (in_array($startDate, $leaveData['rejected'])) {
                                $workType = 'RL';
                            } else {
                                $workType = 'NA';
                            }
                        }
                    }

                    $existing = $db->query("SELECT id FROM attendance WHERE user_id = ? AND date = ?", [$user_id, $startDate])->getRowArray();

                    if ($existing) {
                        $db->query("UPDATE attendance SET total_hours = ?, work_status = ? , work_type = ? WHERE user_id = ? AND date = ?", [$totalHours, $workType, $worked, $user_id, $startDate]);
                    } else {
                        $db->query("INSERT INTO attendance (user_id, date, total_hours, work_status, work_type) VALUES (?, ?, ?, ?, ?)", [$user_id, $startDate, $totalHours, $workType, $worked]);
                        log_message('info', "✅ Processed: $name ($user_id) - $startDate - $totalHours ($workType)");
                    }

                    echo $user_id . ' ' . $name . ' ' . $type . ' ' . $startDate . ' ' . $totalHours . ' ' . $workType . "</br>";

                    $startDate = date('Y-m-d', strtotime("$startDate +1 day"));
                }
            }
        }

        return true;
    }

    /** checking laeves */
    private function getThisOELeaves($db, $empID, $startDate, $endDate)
    {
        $builder = $db->table('leave_request');
        $builder->select('start_date, end_date, reason, total_num_leaves, emp_id, status');
        $builder->where('emp_id', $empID);
        $builder->where('start_date >=', $startDate);
        $builder->where('end_date <=', $endDate);
        $rows = $builder->get()->getResultArray();

        $leaveRequests = [
            'approved' => [],
            'rejected' => [],
            'pending' => [],
        ];

        foreach ($rows as $row) {
            $status = strtolower($row['status']);
            $current = strtotime($row['start_date']);
            $end = strtotime($row['end_date']);

            while ($current <= $end) {
                $dateStr = date('Y-m-d', $current);

                if ($status === 'approved') {
                    $leaveRequests['approved'][] = $dateStr;
                } elseif ($status === 'rejected') {
                    $leaveRequests['rejected'][] = $dateStr;
                } elseif ($status === 'pending') {
                    $leaveRequests['pending'][] = $dateStr;
                }

                $current = strtotime('+1 day', $current);
            }
        }

        return $leaveRequests;
    }

    /**
     * ----------------------------- function for night watch mans -------------------------------
     */
    public function outDoarstaffTimeCalculation($in = null, $out = null, $type = null)
    {

        $inTime = new DateTime($in ?? '2025-05-27 18:30:00');
        $outTime = new DateTime($out ?? '2025-05-28 09:30:00');

        if ($outTime < $inTime) {
            // Punch-out is before punch-in, likely a data error
            return 'Invalid punch times';
        }

        $interval = $inTime->diff($outTime);
        $hours = $interval->h + ($interval->d * 24);
        $minutes = $interval->i;

        $data = sprintf('%02d:%02d', $hours, $minutes);
        return $this->response->setJSON($data);
    }

    public function addOverMidnight()
    {
        $first = $this->request->getPost('first');
        $second = $this->request->getPost('second');
        $date = $this->request->getPost('date');
        $userid = $this->request->getPost('useid');

        // Convert time strings to DateTime objects
        $firstTime = DateTime::createFromFormat('H:i', $first);
        $secondTime = DateTime::createFromFormat('H:i', $second);

        // Calculate 24:00 - second
        $midnight = DateTime::createFromFormat('H:i', '24:00');
        $interval = $secondTime->diff($midnight);

        // Add the interval to the first time
        $resultTime = clone $firstTime;
        $resultTime->add($interval);

        $formatedTime = trim($resultTime->format('H:i'));

        $attendancemodel = new AttendanceModel;
        $updated = $attendancemodel->set(['total_hours' => $formatedTime, 'work_type' => 'NWM'])->where(['user_id' => $userid, 'date' => $date])->update();
        $msg = 'failed';
        if ($updated) {
            $msg = 'success';
        }


        return $this->response->setJSON(['time' => trim($formatedTime), 'message' => $msg]);
    }
}
