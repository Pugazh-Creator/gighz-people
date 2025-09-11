<?php

namespace App\Models;

use App\Controllers\PayRole;
use CodeIgniter\Model;
use DateTime;

class AttendanceModel extends Model
{
    protected $table            = 'attendance';
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
        $db = \Config\Database::connect();
        $this->allowedFields = $this->getAllColumns();
    }

    private function getAllColumns()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($this->table);
        return $fields;
    }

    public function getAllAttendance()
    {
        return $this->orderBy('date', 'DESC')->findAll();
    }

    public function getLeaves($empId, $startDate, $endDate)
    {
        return $this->db->table('attendance')
            ->select('attendance.*, attendance_users.emp_id')
            ->join('attendance_users', 'attendance.user_id = attendance_users.user_id')
            ->where('attendance.work_status', 'Absent')
            ->where('attendance_users.emp_id', $empId)
            ->where('attendance.date >=', $startDate)
            ->where('attendance.date <=', $endDate)
            ->orderBy('attendance.date', 'ASC')
            ->get()->getResultArray();
    }

    public function getStartAndEndDate($selectedMonth = null, $selectedYear = null)
    {
        // Check if the user manually selected a month
        $isManualSelection = !is_null($selectedMonth) && !is_null($selectedYear);

        $selectedMonth = $selectedMonth ?? date('m');
        $selectedYear = $selectedYear ?? date('Y');

        if (!$isManualSelection) {
            // Only apply OE logic when user has NOT manually selected a month
            if ($selectedMonth == 1 && date('d') <= 24) {
                // If it's January (1st to 24th), the range is from Dec 25th to Jan 24th
                $currentYear = $selectedYear - 1;
                $currentMonth = 12;
            } elseif (date('d') <= 24) {
                // If current date is within 1st-24th, use the previous month
                $currentYear = $selectedYear;
                $currentMonth = $selectedMonth - 1;
            } else {
                // If it's 25th or later, use the current month
                $currentYear = date('Y');
                $currentMonth =  date('m');
            }
        } else {
            // If user selected a specific month, use it as-is
            if ($selectedMonth == 1) {
                // If it's January (1st to 24th), the range is from Dec 25th to Jan 24th
                $currentYear = $selectedYear - 1;
                $currentMonth = 12;
            } else {
                $currentYear = $selectedYear;
                $currentMonth = $selectedMonth - 1;
            }
        }

        $nextMonth = ($currentMonth == 12) ? 1 : $currentMonth + 1;
        $nextYear = ($currentMonth == 12) ? $currentYear + 1 : $currentYear;

        $data = [
            'startDate' => "$currentYear-$currentMonth-25",
            'endDate' => "$nextYear-$nextMonth-24"
        ];

        return $data;
    }
    /**
     * Getting this OE Attendance Datas
     */
    public function getMonthlyAttendance($selectedMonth = null, $selectedYear = null)
    {

        $dates = $this->getStartAndEndDate($selectedMonth, $selectedYear);
        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];


        $query = $this->db->table('attendance')
            ->select('attendance.user_id, date, total_hours,work_status, work_type, attendance_users.name, attendance_users.emp_id, attendance_users.employee_status')
            ->join('attendance_users', 'attendance_users.user_id = attendance.user_id')
            // ->join('employees', 'employees.emp_id = attendance_users.emp_id')
            // ->orderBy('employees.dept', 'ASC')
            ->where('attendance_users.employee_status', '1')
            ->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->orderBy('date', 'ASC')
            ->get();

        $attendanceData = [];
        $dates = [];

        foreach ($query->getResultArray() as $row) {
            $userId = $row['user_id'];
            $date = $row['date'];
            $totalHours = $row['total_hours'];
            $name = $row['name'];
            $workStatus = $row['work_status'];
            $worktype = $row['work_type'];
            $empId = $row['emp_id'];

            if (!isset($attendanceData[$userId])) {
                $attendanceData[$userId] = [
                    'emp_id' => $empId,
                    'name' => $name,
                    'records' => [],
                    'totalWorkMinutes' => 0,  // Will store total OE minutes
                    'presentDays' => 0
                ];
            }
            // if ($workStatus == 'Absent') {
            //     $totalHours = 'Absent';
            // } else if ($workStatus == 'OD') {
            //     $totalHours = 'OD';
            // } else if ($workStatus == 'WFH') {
            //     $totalHours = 'WFH';
            // } else if ($workStatus == 'NA') {
            //     $totalHours = 'NA';
            // } else if ($workStatus == 'APL') {
            //     $totalHours = 'APL';
            // }

            $attendanceData[$userId]['records'][$date]['workstatus'] = $workStatus;
            $attendanceData[$userId]['records'][$date]['worktype'] = $worktype;
            $attendanceData[$userId]['records'][$date]['workhours'] = $totalHours;
            $dates[$date] = true;
            // Check if the user is present (i.e., has recorded working hours and not "Absent")
            if (
                !empty($workStatus) && strtolower($workStatus) !== 'absent' && strtolower($workStatus) !== 'apl' && strtolower($workStatus) !== 'na' //&& strtolower($totalHours) !== 'error'
            ) {
                $attendanceData[$userId]['presentDays']++; // Increment present day count

                // Ensure $totalHours is in "HH:MM" format
                if (strpos($totalHours, ':') !== false) {
                    list($hours, $minutes) = explode(':', $totalHours);
                    $hours = is_numeric($hours) ? (int) $hours : 0;
                    $minutes = is_numeric($minutes) ? (int) $minutes : 0;
                } else {
                    $hours = 0;
                    $minutes = 0;
                }

                // Convert to total minutes
                $workingMinutes = ($hours * 60) + $minutes;

                // Accumulate total work minutes
                $attendanceData[$userId]['totalWorkMinutes'] += $workingMinutes;
            }

            // Convert total work minutes to HH:MM format
            $totalMinutes = $attendanceData[$userId]['totalWorkMinutes'];
            $totalHours = floor($totalMinutes / 60);  // Get hours
            $totalRemainingMinutes = $totalMinutes % 60; // Get remaining minutes

            $attendanceData[$userId]['totalOEHours'] = sprintf('%02d:%02d', $totalHours, $totalRemainingMinutes);
        }

        return ['data' => $attendanceData, 'dates' => array_keys($dates), 'start' => $startDate, 'end' => $endDate];
    }


    public function getEmployeesLeaves($empId, $selectedMonth = null, $selectedYear = null)
    {
        $compensationModel = new CompensationModel;
        $comapnyHolidayModel = new CompanyHolidayModel;
        $leaveRequest = new LeaveRquestModel();
        $comapnyHolidayModel = new CompanyHolidayModel;

        $dates = $this->getStartAndEndDate($selectedMonth, $selectedYear);
        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];


        $query = $this->db->table('attendance')
            ->select('attendance.user_id, date, total_hours,work_status, attendance_users.name, attendance_users.emp_id, 
                employees.dept, employees.leave_grade')
            ->join('attendance_users', 'attendance_users.user_id = attendance.user_id')
            ->join('employees', 'employees.emp_id = attendance_users.emp_id')
            // ->join('leave_request', 'leave_request.emp_id = attendance_users.emp_id')
            ->orderBy('employees.dept', 'ASC')
            ->where('employees.emp_id', $empId)
            ->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->orderBy('date', 'ASC')
            ->get();


        $attendanceData = [];
        $dates = [];
        $userId = "";
        foreach ($query->getResultArray() as $row) {
            $userId = $row['user_id'];
            $date = $row['date'];
            $totalHours = $row['total_hours'];
            $name = $row['name'];
            $workStatus = $row['work_status'];
            $empId = $row['emp_id'];
            $leaveGrade = $row['leave_grade'];

            if (!isset($attendanceData[$userId])) {
                $attendanceData[$userId] = [
                    'emp_id' => $empId,
                    'name' => $name,
                    'records' => [],
                    'totalWorkMinutes' => 0,  // Will store total OE minutes
                    'presentDays' => 0,
                    'absentDays' => 0,
                    'compensation' => 0,
                    'otherSaterday' => 0,
                    'not_applied' => 0,
                    'rejected_leave' => 0,
                    'approved_leave' => 0,
                    'od' => 0,
                    'wfh' => 0,
                    'permission_hours' => 0,
                    'actual_present' => 0,
                    'sortfall' => 0,

                ];
            }


            // $attendanceData[$userId]['records'][$date] = $totalHours;
            $dates[$date] = true;
            // Check if the user is present (i.e., has recorded working hours and not "Absent")
            if (!empty($totalHours) && strtolower($workStatus) !== 'absent' && strtolower($workStatus) !== 'apl' && strtolower($workStatus) !== 'rl' && strtolower($workStatus) !== 'na') {                 //&& strtolower($totalHours) !== 'error'
                $attendanceData[$userId]['presentDays']++; // Increment present day count

                $holidays = $comapnyHolidayModel->getOtherSaturdays();

                $holidayDates = array_column($holidays, 'holiday_date');

                if (in_array($date, $holidayDates) && $leaveGrade == '2') {
                    $attendanceData[$userId]['otherSaterday']++;
                }

                // Ensure $totalHours is in "HH:MM" format
                if (strpos($totalHours, ':') !== false) {
                    list($hours, $minutes) = explode(':', $totalHours);
                    $hours = is_numeric($hours) ? (int) $hours : 0;
                    $minutes = is_numeric($minutes) ? (int) $minutes : 0;
                } else {
                    $hours = 0;
                    $minutes = 0;
                }

                // Convert to total minutes
                $workingMinutes = ($hours * 60) + $minutes;

                // Accumulate total work minutes
                $attendanceData[$userId]['totalWorkMinutes'] += $workingMinutes;
            } elseif (!empty($workStatus)) {

                if(strtolower($workStatus) == 'apl'){
                    $attendanceData[$userId]['approved_leave']++;
                    $attendanceData[$userId]['absentDays']++;
                }
                if(strtolower($workStatus) == 'rl'){
                    $attendanceData[$userId]['rejected_leave']++;
                    $attendanceData[$userId]['absentDays']++;
                }
                if(strtolower($workStatus) == 'na'){
                    $attendanceData[$userId]['not_applied']++;
                    $attendanceData[$userId]['absentDays']++;
                }

                // if ($row['leave_grade'] == 3) {
                //     $holidayExists = $comapnyHolidayModel->where('holiday_date', $date)->first();
                // } else {
                //     $holidayExists = $comapnyHolidayModel->where('holiday_date', $date)
                //         ->whereIn('holiday_type', ['festival', 'first_saturday'])
                //         ->first();
                // }

                // $isSunday = (date('w', strtotime($date)) == 0);
                // if (!$holidayExists && !$isSunday) {
                //     $leavesRequest = $leaveRequest->getThisOELeaves($empId, $startDate, $endDate);
                //     $leaveBoolean = false;
                //     if ($leavesRequest) {
                //         // Check if the date exists in rejected leaves
                //         if (in_array($date, $leavesRequest['rejected'])) {
                //             $attendanceData[$userId]['rejected_leave']++;
                //             $workStatus = 'RL';
                //             $leaveBoolean = true;
                //             // echo "rejected : $date </br>";
                //         }
                //         // Check if the date exists in approved leaves
                //         elseif (in_array($date, $leavesRequest['approved'])) {
                //             $attendanceData[$userId]['approved_leave']++;
                //             $workStatus = 'APL';
                //             // echo "Approved : $date </br>";
                //             $leaveBoolean = true;
                //         } else {
                //             $attendanceData[$userId]['not_applied']++;
                //             $workStatus = 'NA';
                //             $leaveBoolean = true;
                //         }
                //     }

                //     if (!$leaveBoolean) {
                //         $attendanceData[$userId]['not_applied']++;
                //         $workStatus = 'NA';
                //     }
                //     $attendanceData[$userId]['absentDays']++;
                // }
            }

            if (strtolower($workStatus) === 'od') {
                $attendanceData[$userId]['od']++;
                $workStatus = 'OD';
            } else if (strtolower($workStatus)  === 'wfh') {
                $attendanceData[$userId]['wfh']++;
                $workStatus = 'WFH';
            }

            $attendanceData[$userId]['records'][$date] = ['total' => $totalHours, 'status' =>  $workStatus];

            $attendanceData[$userId]['compensation'] = $compensationModel->getEmployeeCompensation($empId, $startDate, $endDate);
            // Sum up compensations
            // foreach ($compensations as $compensation) {
            //     $attendanceData[$userId]['compensation'] += $compensation['num_of_days'];
            // }


            // Convert total work minutes to HH:MM format
            $totalMinutes = $attendanceData[$userId]['totalWorkMinutes'];
            $totalHours = floor($totalMinutes / 60);  // Get hours
            $totalRemainingMinutes = $totalMinutes % 60; // Get remaining minutes

            $attendanceData[$userId]['totalOEHours'] = sprintf('%02d:%02d', $totalHours, $totalRemainingMinutes);
        }

        if ($userId != '') {

            $totalMinutes = (int) $attendanceData[$userId]['totalWorkMinutes'];
            $totalHours = floor($totalMinutes / 60);  // Get hours
            $totalRemainingMinutes = $totalMinutes % 60; // Get remaining minutes

            $attendanceData[$userId]['totalOEHours'] = sprintf('%02d:%02d', $totalHours, $totalRemainingMinutes);

            $days = $attendanceData[$userId]['presentDays'];
            $hoursPerDay = 8;

            $totalHours = $days * $hoursPerDay; // 64
            // $totalMinutes = $totalHours * 60;   // Optional if you need minutes too
            $payrollcontroller = new PayRole;

            $permissionminutes = (int) $payrollcontroller->getemppermission($empId, $startDate, $endDate);
            $attendanceData[$userId]['permission_hours'] = sprintf('%02d:%02d', floor($permissionminutes / 60), $permissionminutes % 60);


            $totalMinutes = ($totalHours * 60) - $permissionminutes;

            $totalhour = floor($totalMinutes / 60);
            $totalMinutes = $totalMinutes % 60;


            // Convert to HH:MM format
            $hhmm = sprintf('%02d:%02d', $totalhour, $totalMinutes);

            $attendanceData[$userId]['actual_present'] = $hhmm;

            // Convert HH:MM to minutes
            list($h1, $m1) = explode(':', $attendanceData[$userId]['totalOEHours']);
            list($h2, $m2) = explode(':', $hhmm);

            $minutes1 = ($h1 * 60) + $m1;
            $minutes2 = ($h2 * 60) + $m2;

            // Calculate absolute difference

            if ($minutes1 >= $minutes2) {
                $sortfall = '00:00';
            } else {
                $diffMinutes = abs($minutes1 - $minutes2);

                // Convert back to HH:MM format
                $hours = floor($diffMinutes / 60);
                $minutes = $diffMinutes % 60;

                $sortfall = sprintf('%02d:%02d', $hours, $minutes);
            }

            $attendanceData[$userId]['sortfall'] = $sortfall;
        }

        return ['data' => $attendanceData, 'dates' => array_keys($dates)];
    }
}
