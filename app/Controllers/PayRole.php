<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyHolidayModel;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRquestModel;
use CodeIgniter\Database\Query;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use DateTime;
use PhpOffice\Math\Math;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\YearFrac;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\Beta;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Stmt\Continue_;
use React\Dns\Query\RetryExecutor;

class PayRole extends BaseController
{

    public $globlestartdate;
    public $globleenddate;

    public function __construct()
    {
        helper(['url', 'form']);
    }


    public function index()
    {
        $role = session()->get('role');
        if ($role == '' || $role == 3) {
            return view('layouts/restric_page.php');
        }
        return view('payrole/payrole');
    }

    // month and year for Select tag
    public function loadmonthandyear()
    {
        $data['months'] = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
        // Generate year options (last 5 years + next 2 years)
        $currentYear = (int) date('Y');
        $data['years'] = range($currentYear - 5, $currentYear + 2);


        $month = (int) date('m');
        $today = (int) date('d');
        if ($today <= 24) {
            $data['selectedmonth'] = $month - 1 == 0 ? 12 : $month - 1;
            $data['selectedyear'] = $month - 1 == 0 ? $currentYear - 1 : $currentYear;
        } else {
            $data['selectedmonth'] = $month;
            $data['selectedyear'] = $currentYear;
        }

        return $this->response->setJSON($data);
    }

    public function getattendance($selectedMonth, $selectedYear)
    {

        $db = db_connect();
        $conditionMonth = (int) date('m');
        $conditionYear = (int) date('Y');
        $condmonth = $conditionMonth - 1 == 0 ? 12 : $conditionMonth - 1;
        $dates = $this->getStartAndEndDate($selectedMonth, $selectedYear);

        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        if ($condmonth == $selectedMonth) {
            $showtoday = date('Y-m-d');
            $showstartDate = date('Y-m') . "-02";
            $showenddate = date('Y-m') . "-21";

            if (!($showtoday >= $showstartDate && $showtoday <= $showenddate)) {
                return;
            }
        } elseif ($condmonth > $selectedMonth && $conditionYear >= $selectedYear) {

            $today = date('d');

            if ($today <= 24) {
                $newStartDate = (clone new DateTime($endDate))->modify('+1 day');

                // New end date = same day as old end date, but +1 month
                $newEndDate = (clone new DateTime($endDate))->modify('+1 month');
            } else {
                $newStartDate = (clone new DateTime($endDate))->modify('+1 month');

                // New end date = same day as old end date, but +1 month
                $newEndDate = (clone new DateTime($endDate))->modify('+2 month');
            }
            $newStartDateFormatted = $newStartDate->format('Y-m-d');
            $newEndDateFormatted = $newEndDate->format('Y-m-d');

            $exist_payroll = $db->query("SELECT * FROM payrole WHERE DATE(created_at) BETWEEN ? AND ? ", [$newStartDateFormatted, $newEndDateFormatted])->getResultArray();

            $datas = ['data' => $exist_payroll, 'access' => 1];

            return $this->response->setJSON($datas);
        }


        $compensationModel = new CompensationModel;
        $comapnyHolidayModel = new CompanyHolidayModel;
        $leaveRequest = new LeaveRquestModel();
        $employeemodel = new EmployeeModel;

        // $dat =  $this->request->getPost();


        // $selectedMonth = $this->request->getPost('month');
        // $selectedYear = $this->request->getPost('year');



        $employees = $employeemodel->where('emp_status', '1')->orderBy('dept', 'ASC')->findAll();

        $attendanceData = [];
        $dates = [];

        $today = date('d');

        if ($today <= 24) {
            $newStartDate = (clone new DateTime($endDate))->modify('+1 day');

            // New end date = same day as old end date, but +1 month
            $newEndDate = (clone new DateTime($endDate))->modify('+1 month');
        } else {
            $newStartDate = (clone new DateTime($endDate))->modify('+1 month');

            // New end date = same day as old end date, but +1 month
            $newEndDate = (clone new DateTime($endDate))->modify('+2 month');
        }

        // New start date = next day after current end date

        // Format if needed
        $newStartDateFormatted = $newStartDate->format('Y-m-d');
        $newEndDateFormatted = $newEndDate->format('Y-m-d');

        $this->globlestartdate = $newStartDateFormatted;
        $this->globleenddate = $newEndDateFormatted;

        // return $this->response->setJSON(['date' => "$newStartDateFormatted - $newEndDateFormatted"]);

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        // Include end date by adding 1 day
        $end->modify('+1 day');

        $interval = $start->diff($end);
        $totalDays = $interval->days;

        foreach ($employees as $emp) {
            $empId = $emp['emp_id'];

            // if($empId == 'GZ53')continue;
            $last_leave_update = $emp['leave_balence_updated'];
            $remainingleaves = $emp['remaining_leaves'];
            $dept = $emp['dept'];

            // if ($empId == 'GZ03') {
            //     return $this->response->setJSON(['status' => 'GZ03 is comming']);
            // }

            $builder = $db->query(
                "SELECT new_pay FROM tbl_increment WHERE increment_employee = ? ORDER BY increment_date DESC LIMIT 1",
                [$empId] // Pass your dynamic employee ID here
            );

            $salary = $builder->getRow(); // or getRowArray() if you prefer array format

            if ($salary) {
                $newPay = $salary->new_pay;
            } else {
                $newPay = 0; // or default value
            }

            $query = $db->table('attendance')
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
                        // 'records' => [],
                        'totalWorkMinutes' => 0,  // Will store total OE minutes
                        'presentDays' => 0,
                        'absentDays' => 0,
                        'compensation' => 0,
                        'otherSaterday' => 0,
                        'not_applied' => 0,
                        'rejected_leave' => 0,
                        'approved_leave' => 0,
                        'od' => 0,
                        'dept' => $dept,
                        'wfh' => 0,
                        'sortfall' => 0,
                        'actual_present' => 0,
                        'perday_sal' => 0,
                        'permission_hours' => 0,
                        'currecnt_salary' => 0,
                        'lop_days' => 0,
                        'netpay' => 0,
                        'old_lop' => 0,
                        'additional' => 0,
                        'additional_data' => [],
                        'deduction_data' => [],
                        'deduction' => 0,
                        'account_no' => 0,
                        'ifsc' => 0,
                        'deduction_details' => [],
                        'additional_details' => [],

                    ];
                }

                $account_details = $db->query("SELECT * FROM bank_details WHERE bank_emp_id = ? LIMIT 1", [$empId])->getResultArray();

                if (!empty($account_details)) {
                    $attendanceData[$userId]['account_no'] = $account_details[0]['bank_account_no'];
                    $attendanceData[$userId]['ifsc'] = $account_details[0]['bank_ifsc'];
                }

                // get employee payments 



                $attendanceData[$userId]['currecnt_salary'] = $newPay;
                $attendanceData[$userId]['perday_sal'] = ($newPay > 0) ? ($newPay / $totalDays) : 0;



                // $attendanceData[$userId]['records'][$date] = $totalHours;
                $dates[$date] = true;
                // Check if the user is present (i.e., has recorded working hours and not "Absent")
                if (!empty($totalHours) && strtolower($workStatus) !== 'absent' && strtolower($workStatus) !== 'apl' && strtolower($workStatus) !== 'na' && strtolower($workStatus) !== 'rl') {

                    $compen = $compensationModel->where('start_date <=', $date)
                        ->where('end_date >= ', $date)
                        ->where('emp_id', $empId)->findAll();
                    if (!empty($compen)) {
                        $attendanceData[$userId]['compensation']++;
                    }
                    //&& strtolower($totalHours) !== 'error'
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

                    $total_lop = $db->table('tbl_lop')
                        ->selectSum('lop_total', 'total_lop')
                        ->where('lop_user', $empId)
                        ->where('lop_date >=', $startDate)
                        ->where('lop_date <=', $endDate)
                        ->get()
                        ->getRowArray();

                    $attendanceData[$userId]['lop_days'] = $total_lop['total_lop'] != null ? $total_lop['total_lop']  : 0;


                    // Convert to total minutes
                    $workingMinutes = ($hours * 60) + $minutes;

                    // Accumulate total work minutes
                    $attendanceData[$userId]['totalWorkMinutes'] += $workingMinutes;
                }

                if (strtolower($workStatus) === 'od') {
                    $attendanceData[$userId]['od']++;
                    $attendanceData[$userId]['absentDays']++;
                    $workStatus = 'OD';
                } else if (strtolower($workStatus)  === 'wfh') {
                    $attendanceData[$userId]['wfh']++;
                    // $attendanceData[$userId]['absentDays']++;
                    $workStatus = 'WFH';
                } else if (strtolower($workStatus)  === 'na') {
                    $attendanceData[$userId]['not_applied']++;
                    $attendanceData[$userId]['absentDays']++;
                } else if (strtolower($workStatus)  === 'apl') {
                    $attendanceData[$userId]['approved_leave']++;
                    $attendanceData[$userId]['absentDays']++;
                } else if (strtolower($workStatus)  === 'rl') {
                    $attendanceData[$userId]['rejected_leave']++;
                    $attendanceData[$userId]['absentDays']++;
                }

                // $attendanceData[$userId]['records'][$date] = ['total' => $totalHours, 'status' =>  $workStatus];

                // $attendanceData[$userId]['compensation'] = $compensationModel->getEmployeeCompensation($empId, $startDate, $endDate);
                // Sum up compensations
                // foreach ($compensations as $compensation) {
                //     $attendanceData[$userId]['compensation'] += $compensation['num_of_days'];
                // }

            }



            $year_month = date('Y-m');
            $str = "$year_month-02";
            $end = "$year_month-15";
            $totaloeleaves = $attendanceData[$userId]['absentDays'];
            $totaloecompensation = $attendanceData[$userId]['compensation'];

            $attendanceData[$userId]['old_lop'] = $attendanceData[$userId]['lop_days'];
            $oelop = $attendanceData[$userId]['lop_days'];
            $leaveUpdatedate = date('Y-m-d');
            if (!($last_leave_update >= $str && $last_leave_update <= $end)) {

                if ($totaloeleaves >= $totaloecompensation) {
                    $remainingleaves += $totaloecompensation;
                } else if ($totaloeleaves < $totaloecompensation) {
                    $remainingleaves += $totaloeleaves;
                }
                $db->query("UPDATE employees SET remaining_leaves = ?, leave_balence_updated = ? WHERE emp_id = ?", [$remainingleaves, $leaveUpdatedate, $empId]);
            }
            if ($totaloeleaves >= $totaloecompensation) {
                $attendanceData[$userId]['lop_days'] = $oelop >= $totaloecompensation ?  $oelop - $totaloecompensation : 0;
            } else if ($totaloeleaves < $totaloecompensation) {
                $attendanceData[$userId]['lop_days'] = $oelop >= $totaloeleaves ? $oelop - $totaloeleaves : 0;
            }


            // Fetch additional credits
            $additional = $db->query(
                "
    SELECT pay_id, pay_type, pay_total_amount AS additional 
    FROM payments 
    WHERE pay_date BETWEEN ? AND ? 
    AND pay_status = ? 
    AND pay_empid = ? 
    AND transaction_type = ? 
    AND payment_type = ? AND pay_type != 'Arrear'
    ",
                [$newStartDateFormatted, $newEndDateFormatted, 'Open', $empId, 'Credit', 'salary']
            )->getResultArray();

            // Fetch arrear credits
            $arrearSql = "
    SELECT pay_id, pay_type, deduction_amount AS deduction
    FROM payments
    WHERE deducted_date BETWEEN ? AND ?
    AND pay_empid = ?
    AND transaction_type = ?
    AND payment_type = ? AND pay_type = 'Arrear'
";

            $arrearAddition = $db->query($arrearSql, [
                $newStartDateFormatted,
                $newEndDateFormatted,
                $empId,
                'Credit',
                'salary'
            ])->getResultArray();

            $total_additional = 0;
            $additional_data = [];
            $additional_detail_data = [];

            // Process normal additions
            foreach ($additional as $entry) {
                $type = $entry['pay_type'];
                $amount = floatval($entry['additional']);

                $additional_detail_data[] = [
                    'id' => $entry['pay_id'],
                    'type' => $type,
                    'payed' => $amount,
                ];

                if (!isset($additional_data[$type])) {
                    $additional_data[$type] = [
                        'type' => $type,
                        'payed' => $amount,
                    ];
                } else {
                    $additional_data[$type]['payed'] += $amount;
                }

                $total_additional += $amount;
            }

            // Process arrear additions
            foreach ($arrearAddition as $entry) {
                $type = $entry['pay_type'];
                $amount = floatval($entry['deduction']); // still a credit, just using different column name

                $additional_detail_data[] = [
                    'id' => $entry['pay_id'],
                    'type' => $type,
                    'payed' => $amount,
                ];

                if (!isset($additional_data[$type])) {
                    $additional_data[$type] = [
                        'type' => $type,
                        'payed' => $amount,
                    ];
                } else {
                    $additional_data[$type]['payed'] += $amount;
                }

                $total_additional += $amount;
            }

            // Assign results to user
            $attendanceData[$userId]['additional'] = $total_additional;
            $attendanceData[$userId]['additional_details'] = $additional_detail_data;
            $attendanceData[$userId]['additional_data'] = array_values($additional_data); // reindex


            // ------------------ DEDUCTION -----------------------
            // ------------------ DEDUCTION -----------------------
            $sql = "
    SELECT 
        pay_id, 
        pay_type, 
        deduction_amount AS deduction
    FROM 
        payments
    WHERE 
        deducted_date BETWEEN ? AND ?
        AND pay_empid = ?
        AND transaction_type = ?
        AND payment_type = ?
";

            $deduction = $db->query($sql, [
                $newStartDateFormatted,
                $newEndDateFormatted,
                $empId,
                'Debit',
                'salary'
            ])->getResultArray();

            $arrearsql = "
    SELECT 
        pay_id, 
        pay_type, 
        pay_total_amount AS amount
    FROM 
        gighz.payments
    WHERE 
        pay_date BETWEEN ? AND ?
        AND pay_empid = ?
        AND transaction_type = ? 
        AND payment_type = ? 
";

            $arreardeduction = $db->query($arrearsql, [
                $newStartDateFormatted,
                $newEndDateFormatted,
                $empId,
                'Hold',
                // 'Debit',
                // 'NA',
                'salary'
            ])->getResultArray();

            $total_deduction = 0;
            $deduction_data = [];
            $deduction_detail_data = [];

            // Process normal deductions
            foreach ($deduction as $d) {
                $type = $d['pay_type'];
                $amount = $d['deduction'];

                $deduction_detail_data[] = [
                    'id' => $d['pay_id'],
                    'type' => $type,
                    'payed' => $amount,
                ];

                if (!isset($deduction_data[$type])) {
                    $deduction_data[$type] = [
                        'type' => $type,
                        'payed' => $amount,
                    ];
                } else {
                    $deduction_data[$type]['payed'] += $amount;
                }

                $total_deduction += $amount;
            }

            // Process arrear-based deductions
            foreach ($arreardeduction as $d) {
                $type = $d['pay_type'];
                $amount = $d['amount'];

                $deduction_detail_data[] = [
                    'id' => $d['pay_id'],
                    'type' => $type,
                    'payed' => $amount,
                ];

                if (!isset($deduction_data[$type])) {
                    $deduction_data[$type] = [
                        'type' => $type,
                        'payed' => $amount,
                    ];
                } else {
                    $deduction_data[$type]['payed'] += $amount;
                }

                $total_deduction += $amount;
            }

            $attendanceData[$userId]['deduction'] = $total_deduction;
            $attendanceData[$userId]['deduction_details'] = $deduction_detail_data;
            $attendanceData[$userId]['deduction_data'] = array_values($deduction_data);


            // net pay 
            if ($attendanceData[$userId]['compensation'] > $totaloeleaves) {
                $netPay = ($totalDays  + $attendanceData[$userId]['compensation'] - $totaloeleaves - $attendanceData[$userId]['lop_days']) * $attendanceData[$userId]['perday_sal'];
            } else {
                $netPay = ($totalDays - $attendanceData[$userId]['lop_days']) * $attendanceData[$userId]['perday_sal'];
            }
            $notice_period = $db->query("select * from hr_exit where emp_id = ? and status != 'Cancelled' ", [$empId])->getResultArray();
            $notice_entry = $db->query("SELECT * FROM payments WHERE pay_date BETWEEN ? AND ? AND pay_notes = ?", [$newStartDateFormatted, $newEndDateFormatted, 'Auto Hold For Notice Period'])->getResultArray();

            if (!empty($notice_period) && empty($notice_entry)) {

                $arrear_datas = [
                    'pay_empid' => $empId,
                    'pay_type' => 'Arrear',
                    'pay_total_amount' => $netPay,
                    'balence_amount' => $netPay,
                    'payment_type' => 'NA',
                    'transaction_type' => 'Hold',
                    'pay_notes' => 'Auto Hold For Notice Period',
                    'pay_date' => date('Y-m-d'),
                ];

                $db->table('payments')->insert($arrear_datas);

                $netPay = 0;
            }

            $attendanceData[$userId]['netpay'] = $netPay;

            // Convert total work minutes to HH:MM format
            $totalMinutes = (int) $attendanceData[$userId]['totalWorkMinutes'];
            $totalHours = floor($totalMinutes / 60);  // Get hours
            $totalRemainingMinutes = $totalMinutes % 60; // Get remaining minutes

            $attendanceData[$userId]['totalOEHours'] = sprintf('%02d:%02d', $totalHours, $totalRemainingMinutes);

            $days = $attendanceData[$userId]['presentDays'];
            $hoursPerDay = 8;

            $totalHours = $days * $hoursPerDay; // 64
            // $totalMinutes = $totalHours * 60;   // Optional if you need minutes too

            $permissionminutes = (int) $this->getemppermission($empId, $startDate, $endDate);
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
        $datas =  ['data' => $attendanceData, 'dates' => array_keys($dates), 'monthdays' => $totalDays];
        $datas['access'] = 0;

        return $this->response->setJSON($datas);
    }



    public function getemppermission($empid, $startDate, $endDate)
    {
        $db = db_connect();

        $permission_hrs = $db->query("SELECT * FROM permission_hrs WHERE permission_user_id =? AND permission_date BETWEEN ? AND ?", [$empid, $startDate, $endDate])->getResultArray();

        $total_permission = 0;
        foreach ($permission_hrs as $r) {
            $hours = $r['permission_time'];

            list($h, $m) = explode(':', $hours);

            $total_permission += ($h * 60) + $m;
        }

        return $total_permission;
    }


    /**
     * 
     * ----------------------------- get this OE Dates ------------------------ 
     *  
     */
    public function getStartAndEndDate($selectedMonth, $selectedYear)
    {
        // Check if the user manually selected a month
        $isManualSelection = !is_null($selectedMonth) && !is_null($selectedYear);

        $date = (int) date('m');
        $dates = $date - 1 === 0 ? 12 : $date - 1;

        $selectedMonth = $selectedMonth ?? $dates;
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

        // return $data;
        return $this->response->setJSON($data);
    }

    public function storeOtherSaturdays()
    {
        $year = date('Y');
        $db = new CompanyHolidayModel; // CodeIgniter style DB connection

        for ($month = 1; $month <= 12; $month++) {
            $saturdays = [];

            // Get all days in the month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = "$year-$month-$day";
                if (date('N', strtotime($date)) == 6) { // 6 = Saturday
                    $saturdays[] = $date;
                }
            }

            // Store 3rd and 5th if exists, else 3rd and 4th
            if (count($saturdays) >= 5) {
                $toStore = [$saturdays[2], $saturdays[4]];
            } else {
                $toStore = [$saturdays[2], $saturdays[3] ?? null];
            }

            foreach ($toStore as $saturday) {
                if ($saturday) {

                    $monthName = date('F', strtotime($saturday));
                    $dayName   = date('l', strtotime($saturday));
                    $data = [
                        'holiday_date' => $saturday,
                        'holiday_name' => 'Other Saturday',
                        'month' => $monthName,
                        'day' => $dayName,
                        'holiday_type' => 'other_saturday',
                    ];
                    $db->insert($data);
                }
            }
        }

        echo "Other Saturdays stored successfully!";
    }

    //loading type of pays in select tag
    public function loadtypeofpays($type)
    {
        if ($type == '0') {
            $data = [
                'Arrear' => 'Arrear',
                'OT' => 'OT',
                'Loan' => 'Loan',
                'Incentive' => 'Incentive',
                'Variable' => 'Variable Pay',
                'Additional' => 'Additional Pay',
                'Advance' => 'Salary Advance',
            ];
        } else {
            $data = [
                'Arrear' => 'Arrear',
                'Loan' => 'Loan',
                'Advance' => 'Salary Advance',
                'Non_Performance' => 'Non Performance',
            ];
        }


        return $this->response->setJSON($data);
    }


    // getting payemts details
    public function get_additional_pay_details()
    {
        $db = db_connect();

        $emp_id = $this->request->getPost('emp_id');
        $type = $this->request->getPost('type');

        if ($emp_id == '' || $type == '') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'You sending empty values']);
        }


        $exist_amount = $db->query(
            "SELECT SUM(balence_amount) AS amount FROM payments WHERE pay_status = ? AND pay_type = ? AND pay_empid = ?",
            ['Open', $type, $emp_id]
        )->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $exist_amount[0]['amount']]);
    }




    //store payments details 
    public function savepayments()
    {
        $db = db_connect();
        $today = date('Y-m-d');

        // log_message('debug', print_r($_POST, true));

        $getbody = $this->request->getPost();



        $empId = $getbody['empid'];
        $amount = $getbody['amount'];
        $type = $getbody['type'];
        $date = $getbody['date'] ?? $today;
        $payment_type = $getbody['payment'];
        $transaction_type = $getbody['trans'];
        $note = $getbody['note'];


        // return $this->response->setJSON(['data' => $getbody]);


        if (($transaction_type == 'Credit' && $type != 'Arrear') || $transaction_type == 'Hold' || $type == 'Non_Performance') {
            // return $this->response->setJSON(['types' => "$transaction_type",'mag' => 'if statement']);
            $data = [
                'pay_empid' => $empId,
                'pay_type' => $type,
                'pay_total_amount' => $amount,
                'balence_amount' => $amount,
                'pay_date' => $date,
                'payment_type' => $payment_type,
                'transaction_type' => $transaction_type,
                'pay_notes' => $note,
                'pay_status' => 'Open',
            ];

            if ($db->table('payments')->insert($data)) {
                return $this->response->setJSON(['status' => 'success',  'msg' => 'addition completed']);
            }

            return $this->response->setJSON(['status' => 'error',  'msg' => 'addition failed']);
        } else {
            // return $this->response->setJSON(['types' => "$transaction_type",'mag' => 'else statement']);
            $amounts = (float)$amount;
            $deducted = false; // Track if anything was deducted

            while ($amounts > 0) {
                $exist_amount = $db->query("SELECT * FROM payments WHERE pay_empid = ? AND pay_type = ? AND pay_status = ? ORDER BY pay_date ASC LIMIT 1", [$empId, $type, 'Open'])->getResultArray();

                if (empty($exist_amount)) {
                    break; // Prevent infinite loop
                }

                $record = $exist_amount[0];
                $balence = (float)$record['balence_amount'];
                $id = $record['pay_id'];

                $deduct = min($amounts, $balence);
                $newbalence = $balence - $deduct;
                $newstatus = $newbalence == 0 ? 'Closed' : 'Open';

                $db->table('payments')
                    ->set(['balence_amount' => $newbalence, 'pay_status' => $newstatus])
                    ->where('pay_id', $id)
                    ->update();

                $data = [
                    'pay_empid' => $record['pay_empid'],
                    'pay_type' => $record['pay_type'],
                    'pay_total_amount' => $record['pay_total_amount'],
                    'deduction_amount' => $deduct,
                    'balence_amount' => $newbalence,
                    'pay_date' => $record['pay_date'],
                    'payment_type' => $payment_type,
                    'transaction_type' => $transaction_type,
                    'pay_notes' => $note,
                    'deducted_date' => $date,
                ];
                $db->table('payments')->insert($data);

                $amounts -= $deduct;
                $deducted = true;
            }

            // Respond based on whether any deduction was done
            if ($deducted) {
                return $this->response->setJSON(['status' => 'success', 'msg' => 'deduction completed']);
            } else {
                return $this->response->setJSON(['status' => 'no_open_balance',  'msg' => 'deduction Filed']);
            }
        }
    }

    //--------------- Write NEFT ---------------

    public function generateNeftDocument()
    {
        $data = $this->request->getPost('data');

        $today = date('Y-m-d');
        $day = date('d');
        $targetDate = ((int)$day <= 24) ? date('Y-m-d', strtotime('-1 month')) : $today;
        $month = date('F-Y', strtotime($targetDate));

        $templatePath = WRITEPATH . 'templates/IDBI Neft Letter MonthYear.docx';
        $duplicatePath = WRITEPATH . "exports/IDBI Neft Letter MonthYear $month.docx";

        $templateProcessor = new TemplateProcessor($templatePath);

        // Set single values
        $templateProcessor->setValue('month', $month);
        $templateProcessor->setValue('date', $today);

        // Clone table rows for each employee
        $total = 0;
        $templateProcessor->cloneRow('s_no', count($data));
        foreach ($data as $index => $row) {
            $i = $index + 1;
            $templateProcessor->setValue("s_no#{$i}", $i);
            $templateProcessor->setValue("name#{$i}", $row['name']);
            $templateProcessor->setValue("ac#{$i}", $row['account']);
            $templateProcessor->setValue("ifsc#{$i}", $row['ifsc']);
            $templateProcessor->setValue("netpay#{$i}", round($row['netpay']));
            $total += round($row['netpay']);
        }

        $templateProcessor->setValue('total', $total);

        // Save to new path
        $templateProcessor->saveAs($duplicatePath);

        // Return for download
        return $this->response->download($duplicatePath, null);
    }

    public function generateNeftExcel()
    {
        $data = $this->request->getPost('data');


        // $data = [
        //     ['emp_id' => 'GZ001', 'name' => 'Magendiran', 'netpay' => 42000, 'account' => 1010101010, 'ifsc' => 'IDBI0024F'],
        //     ['emp_id' => 'GZ002', 'name' => 'Priya', 'netpay' => 38500, 'account' => 1010101010, 'ifsc' => 'IDBI0024F'],
        //     ['emp_id' => 'GZ003', 'name' => 'Arun', 'netpay' => 45000, 'account' => 1010101010, 'ifsc' => 'IDBI0024F'],
        // ];


        $today = date('Y-m-d');
        $day = date('d');
        $targetDate = ((int)$day <= 24) ? date('Y-m-d', strtotime('-1 month')) : $today;
        $month = date('F-Y', strtotime($targetDate));

        $templatePath = WRITEPATH . 'templates/Bulk Neft IDBI Salary for MonthYear.xlsx';
        $exportPath = WRITEPATH . "exports/Bulk Neft IDBI Salary for MonthYear $month.xlsx";

        // Load the Excel template
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Replace F2 -> ${date}
        $f2Val = $sheet->getCell('F2')->getValue();
        $sheet->setCellValue('F2', str_replace('${date}', $today, $f2Val));

        // Replace D10 -> ${month}
        $d10Val = $sheet->getCell('D10')->getValue();
        $sheet->setCellValue('D10', str_replace('${month}', $month, $d10Val));

        // Start inserting employee data from row 12
        $total = 0;
        $totalrow = 0;
        $startRow = 13;
        foreach ($data as $index => $row) {
            $rowNum = $startRow + $index;
            $sheet->setCellValue("B$rowNum", $index + 1);
            $sheet->setCellValue("C$rowNum", $row['name']);
            $sheet->setCellValue("D$rowNum", $row['account']);
            $sheet->setCellValue("E$rowNum", $row['ifsc']);
            $sheet->setCellValue("F$rowNum", round($row['netpay']));

            $total +=  round($row['netpay']);
            $totalrow = $rowNum + 1;
        }
        if ($totalrow != 0) {
            $sheet->setCellValue("F$totalrow", "Total: $total");
        }


        // Save the updated Excel file
        $writer = new Xlsx($spreadsheet);
        $writer->save($exportPath);

        // Trigger file download
        return $this->response->download($exportPath, null);
    }

    // connecting HR Mail 
    public function connecthrmail($to, $subject, $message, $attached_file)
    {
        $email = \Config\Services::email();
        $email->clear(true);

        // $to = 'magendiran.m@gighz.net';

        // Custom SMTP config directly in function
        $config = [
            'protocol'    => 'smtp',
            'SMTPHost'    => 'smtp.hostinger.com',
            'SMTPUser'    => 'hr@gighz.net',
            'SMTPPass'    => 'GigHz123#',
            'SMTPPort'    => 587, // or 465 if using SSL
            'SMTPCrypto'  => 'tls', // or 'ssl'
            'mailType'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
        ];

        $email->initialize($config);

        // Compose email
        $email->setFrom('hr@gighz.net', 'HR');
        $email->setTo($to);
        // $email->setCC('chandra.mohan@gighz.net');
        $email->setSubject($subject);
        $email->setMessage($message);

        if (file_exists($attached_file)) {
            $email->attach($attached_file);
        } else {
            log_message('error', 'Attachment file not found: ' . $attached_file);
        }

        // Send email
        if ($email->send()) {
            return;
        } else {
            echo 'Email failed to send.<br>';
            echo $email->printDebugger(['headers']);
        }
    }

    /** 
     * - -------------- send Pay slips  -------------------- 
     */

    public function sendPayslipToEmail()
    {
        $db = db_connect();
        $post_data = $this->request->getPost('data');

        // return $this->response->setJSON($post_data);

        // $emp_id = $this->request->getPost('emp_id') ?? 'GZ44';
        // $name = $this->request->getPost('name') ?? 'Magi';
        // $email = $this->request->getPost('email') ?? 'xocogih263@ihnpo.com';
        // $netpay = $this->request->getPost('netpay') ?? '25000';
        // $month = $this->request->getPost('month') ?? 'May-2025';

        $today = date('Y-m-d');
        $day = date('d');
        $targetDate = ((int)$day <= 24) ? date('Y-m-d', strtotime('-1 month')) : $today;
        $month = date('F-Y', strtotime($targetDate));

        foreach ($post_data as $row) {
            $paiddays = $row['paiddays'];
            $r = $row['datas'];
            $emp_id = $r['emp_id'];
            // $name = $r['name'];
            $ac_no = $r['account_no'];
            $lop_days = $r['lop_days'];
            $netpay = (float) $r['netpay'];

            $employee_details = $db->query("SELECT e.*, d.dept_name, t.position_name FROM employees e
                    JOIN department d ON d.dept_id = e.dept
                    JOIN tbl_position t ON t.position_id = e.designation
                     WHERE e.emp_id = ? LIMIT 1", [$emp_id])->getRowArray();

            if (empty($employee_details)) {
                continue;
            }
            return $this->response->setJSON(['emp_id' => $employee_details]);
            $name = $employee_details['name'];
            $unsent = [];
            if (!empty($employee_details['official_mail'])) {
                $email = $employee_details['official_mail'];
            } else {
                $unsent[] = $name . " " . $emp_id;
                continue;
            }


            $templatePath = WRITEPATH . 'templates/emp_id - MonthYear Payslip.xlsx';
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();



            // Replace placeholders
            $sheet->setCellValue("E9", $employee_details['dept_name']);
            $sheet->setCellValue("C10", $employee_details['doj']);
            $sheet->setCellValue("C12", $employee_details['dob']);
            $sheet->setCellValue("E12", $employee_details['pan']);
            $sheet->setCellValue("C11", $paiddays);
            $sheet->setCellValue("E10", $ac_no);
            $sheet->setCellValue("E11", $lop_days);
            $sheet->setCellValue("D6", " $month");
            $sheet->setCellValue("C9",  $employee_details['position_name']);
            $sheet->setCellValue("C8", $name);
            $sheet->setCellValue("E8", $emp_id);

            $basic_sal = $netpay != 0 ? $netpay / 2 : 0;
            $house_rent = $basic_sal != 0 ? $basic_sal / 2 : 0;
            $conveyance_pay = ($netpay - ($basic_sal + $house_rent)) >= 1250 ? 1250 : 0;
            $special_pay = $netpay - ($basic_sal + $house_rent + $conveyance_pay);

            $totalNetPay = $basic_sal + $house_rent + $conveyance_pay + $special_pay;


            $sheet->setCellValue("C14", "₹" . round($basic_sal));
            $sheet->setCellValue("C15", "₹" . round($house_rent));
            $sheet->setCellValue("C16", "₹" . round($conveyance_pay));
            $sheet->setCellValue("C18", "₹" . round($special_pay));

            $additional_performence = 0;
            $additional_salary_advance = 0;
            $additional_arrear_others = 0;
            $additional_loan = 0;

            if (!empty($r['additional_data'])) {
                foreach ($r['additional_data'] as $additional_pay) {
                    $paied = (float) $additional_pay['payed'];
                    $type = $additional_pay['type'];

                    if (strtolower($type) == "advance") {
                        $additional_salary_advance += $paied;
                    } else if (strtolower($type) == "incentive") {
                        $additional_performence += $paied;
                    } else if (strtolower($type) == "loan") {
                        $additional_loan += $paied;
                    } else {
                        $additional_arrear_others += $paied;
                    }
                }
            }

            $totalAdditionalPays = round($additional_performence + $additional_salary_advance + $additional_arrear_others + $additional_loan);

            $sheet->setCellValue("C19", "₹" . round($additional_performence));
            $sheet->setCellValue("C20", "₹" . round($additional_salary_advance));
            $sheet->setCellValue("C21", "₹" . round($additional_arrear_others));
            $sheet->setCellValue("C22", "₹" . round($additional_loan));

            $deduction_non_performence = 0;
            $deduction_salary_advance = 0;
            $deduction_arrear_others = 0;
            $deduction_fhhold = 0;
            $deduction_loan = 0;

            if (!empty($r['deduction_data'])) {

                foreach ($r['deduction_data'] as $additional_pay) {
                    $paied = (float) $additional_pay['payed'];
                    $type = $additional_pay['type'];

                    if (strtolower($type) == "advance") {
                        $deduction_salary_advance += $paied;
                    } else if (strtolower($type) == "non_performance") {
                        $deduction_non_performence += $paied;
                    } else if (strtolower($type) == "arrear") {
                        $deduction_fhhold += $paied;
                    } else if (strtolower($type) == "loan") {
                        $deduction_loan += $paied;
                    } else {
                        $deduction_arrear_others += $paied;
                    }
                }
            }

            $totalDeductionAmont = round($deduction_non_performence + $deduction_salary_advance + $deduction_arrear_others + $deduction_fhhold + $deduction_loan);

            $sheet->setCellValue("E14", "₹" . round($deduction_non_performence));
            $sheet->setCellValue("E15", "₹" . round($deduction_loan));
            $sheet->setCellValue("E16", "₹" . round($deduction_arrear_others));
            $sheet->setCellValue("E17", "₹" . round($deduction_salary_advance));
            $sheet->setCellValue("E18", "₹" . round($deduction_fhhold));

            $totalAddtionalAndNetpay = round($totalNetPay + $totalAdditionalPays);
            $sheet->setCellValue("C23", "₹$totalAddtionalAndNetpay");
            $sheet->setCellValue("E23", "₹$totalDeductionAmont");

            $total_amount = round($totalAddtionalAndNetpay - $totalDeductionAmont);
            $sheet->setCellValue("C24", "₹$total_amount");


            // Set PDF renderer to Dompdf
            // \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', Dompdf::class);
            \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', Mpdf::class);

            $spreadsheet->getDefaultStyle()->getFont()->setName('DejaVu Sans');
            // $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');

            // Save as PDF
            $filename = "{$emp_id} - {$month} Payslip.pdf";
            $pdfPath = WRITEPATH . "exports/$filename";
            $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
            $writer->save($pdfPath);


            $to = $email;
            // $to = 'xocogih263@ihnpo.com';
            $subject = "Payslip for $month";
            $message = "<p>Dear $name </p></br>
            <p> Please find attached the payslip for $month.</p></br>
            <p>If you have any questions, feel free to reach out.</p></br></br>
            </br>
            <p><strong>Best regards,</strong></p></br>
            <p>HR</p></br>
            <p>GigHz IT Solution</p>
            ";
            // $attached_file = $pdfPath;



            if (file_exists($pdfPath)) { //&& $emp_id == 'GZ44'
                $this->connecthrmail($to, $subject, $message, $pdfPath); // helper Method
            } else {
                $unsent[] = ['name' => $name, 'emp_id' => $emp_id, 'msg' => 'file not found'];
            }
        }
        // return $this->response->download($pdfPath, null);
        // if (file_exists($pdfPath)) {
        //     return $this->response->download($pdfPath, null)->setFileName($filename);
        // }
        // return $this->response->setStatusCode(404)->setBody("Payslip not found");
        // return $this->response->setJSON(['data' => $post_data]);
        return $this->response->setJSON(['status' => 'success']);

        // Email it
        // $emailService = \Config\Services::email();

    }

    public function editadditionaldata($id, $value)
    {
        $db = db_connect();

        // $id = $this->request->getPost();
        // $value = $this->request->getPost('value');

        // return $this->response->setJSON(['data' => $id]);

        $payments = $db->query("SELECT * FROM payments WHERE pay_id = ?", [$id])->getRowArray();
        $pays = $payments[0];

        // Basic validation
        if (!$id || $value === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid ID or value.'
            ]);
        }
        $builder = $db->table('payments');

        $type = $pays['pay_type'];
        $trans = $pays['transaction_type'];
        if ($type == 'Arrear' && $trans == 'Credit') {
            $updated = $builder->set(['transaction_type' => $value])
                ->where('pay_id', $id)
                ->update();
        }

        $updated = $builder->set(['pay_total_amount' => $value])
            ->where('pay_id', $id)
            ->update();

        if ($updated) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Update failed']);
        }
    }


    public function deleteadditionaldataS($id)
    {
        // return $this->response->setJSON(['data' => $id]);
        $db = db_connect();

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid ID.'
            ]);
        }
        $result = $db->query("SELECT * FROM payments WHERE pay_id =? limit 1", [$id])->getResultArray();
        if (!empty($result[0]['deduction_amount'])) {

            $deducet_bal = $result[0]['deduction_amount'];
            $empId = $result[0]['pay_empid'];
            $type = $result[0]['pay_type'];

            $transtype = $type == 'Arrear' ? 'Hold' : 'Credit';

            $resolve_addition = $db->query(
                "SELECT * FROM payments WHERE transaction_type =? AND pay_type = ? AND pay_empid = ? ORDER BY updated_at DESC",
                [$transtype, $type, $empId]
            )->getResultArray();

            $execution = [];
            foreach ($resolve_addition as $addition) {
                $full_amount = $addition['pay_total_amount'];
                $balence_amount = $addition['balence_amount'];

                if ($deducet_bal == 0) {
                    break;
                }
                $deducted = 0;
                if ($balence_amount != 0 && $full_amount > $balence_amount) {
                    $deducted = min($full_amount - $balence_amount);
                    $deducet_bal = min($deducted - $deducet_bal);
                } else if ($balence_amount == 0 && $full_amount >= $deducet_bal) {
                    $deducted = $deducet_bal;
                    $deducet_bal = 0;
                } else if ($balence_amount == 0 && $full_amount < $deducet_bal) {
                    $deducted = min($full_amount - $deducet_bal);
                    $deducet_bal = $deducted;
                }

                $execution[] = [
                    'deducet_bal' => $deducet_bal,
                    'empId' => $empId,
                    'full_amount' => $full_amount,
                    'balence_amount' => $balence_amount,
                    'deducted' => $deducted
                ];
            }
        }

        return $this->response->setJSON($execution);
        // $deleted = $db->table('payments')->where('pay_id', $id)->delete();

        // if ($deleted) {
        //     return $this->response->setJSON(['status' => 'success']);
        // } else {
        //     return $this->response->setJSON(['status' => 'error', 'message' => 'Delete failed']);
        // }
    }

    public function deleteadditionaldata($id)
    {
        $db = db_connect();

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid ID.'
            ]);
        }

        $result = $db->query("SELECT * FROM payments WHERE pay_id = ? LIMIT 1", [$id])->getRowArray();

        if (empty($result)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No deduction found or invalid payment.'
            ]);
        } elseif (empty($result['deduction_amount'])) {
            $db->table('payments')->where('pay_id', $id)->delete();
            return $this->response->setJSON(['status' => 'it is a credited amount']);
        }

        $deducted_balance = $result['deduction_amount'];
        $paiedamount = $result['pay_total_amount'];
        $empId = $result['pay_empid'];
        $type = $result['pay_type'];
        $payDate = $result['pay_date'];
        $transtype = $type == 'Arrear' ? 'Hold' : 'Credit';

        $datasss = [
            'trsn' => $transtype,
            'type' => $type,
            '$empid' => $empId,
            '$paydate' => $payDate,
            '$amount' => $paiedamount,

        ];

        // return $this->response->setJSON(['data' => $datasss]);

        $resolve_addition = $db->query(
            "SELECT * FROM payments WHERE transaction_type = ? AND pay_type = ? AND pay_empid = ? AND pay_date =? AND pay_total_amount =? LIMIT 1",
            [$transtype, $type, $empId, $payDate, $paiedamount]
        )->getResultArray();

        $execution = [];

        $pay_id = $resolve_addition[0]['pay_id'];
        $full_amount = $resolve_addition[0]['pay_total_amount'];
        $balance = $resolve_addition[0]['balence_amount'];
        $recoverable = $full_amount - $balance;

        $deduct_to_add = min($recoverable, $deducted_balance);
        $new_balance = $balance + $deduct_to_add;
        $deducted_balance -= $deduct_to_add;

        // Optional: Update the database to restore balance
        $db->table('payments')->where('pay_id', $pay_id)->update(['balence_amount' => $new_balance, 'pay_status' => 'Open']);

        $execution[] = [
            'empId' => $empId,
            'pay_id' => $pay_id,
            'full_amount' => $full_amount,
            'previous_balance' => $balance,
            'deducted_restored' => $deduct_to_add,
            'new_balance' => $new_balance,
            'remaining_deducet_bal' => $deducted_balance
        ];

        $db->table('payments')->where('pay_id', $id)->delete();
        return $this->response->setJSON($execution);
    }



    /**
     * Loan page
     */

    // ------------- Department selection --------- 

    public function accounting($emp_id)
    {
        $db = db_connect();
        $data['emp_id'] = $emp_id;

        $result = $db->query("SELECT name FROM employees WHERE emp_id =?", [$emp_id])->getRowArray();
        $data['employeename'] = $result['name'];

        return view('payrole/accounting', $data);
    }

    public function employeeAccounting()
    {

        return view('payrole/employeeaccounts');
    }

    public function getalldeportments()
    {
        $db = db_connect();

        $deportments = $db->query("SELECT dept_id, dept_name FROM department WHERE dept_status = ? ORDER BY dept_name ASC", [1])->getResultArray();
        return $this->response->setJSON($deportments);
    }

    //getting employees based on deportment

    public function getdeportmentemployees()
    {
        $db = db_connect();

        $dept = $this->request->getGet('data');

        $employees = $db->query("SELECT emp_id, name FROM employees WHERE dept = ? AND emp_status = ? ORDER BY NAME ASC", [$dept, '1'])->getResultArray();

        return $this->response->setJSON($employees);
    }

    public function getallpayments()
    {
        $db = db_connect();

        $query = $db->query("SELECT p.*, e.name FROM payments p
            JOIN employees e ON e.emp_id = p.pay_empid
         WHERE pay_type = ? OR pay_type = ? OR pay_type = ?", ['Arrear', 'Loan', 'Advance'])->getResultArray();
        $data = [];



        foreach ($query as $row) {
            $empId = $row['pay_empid'];
            $name = $row['name'];
            $type = $row['pay_type'];
            $total_amount = $row['pay_total_amount'];
            $deducted_amount = $row['deduction_amount'];
            $transaction = $row['transaction_type'];

            if (!isset($data[$empId])) {
                $data[$empId] = [
                    'emp_id' => $empId,
                    'name' => $name,
                    'loan' => 0,
                    'advance' => 0,
                    'arrear' => 0,
                    'loan_addition' => 0,
                    'loan_deduction' => 0,
                    'arrear_addition' => 0,
                    'arrear_deduction' => 0,
                    'advance_addition' => 0,
                    'advance_deduction' => 0,
                    'total' => 0
                ];
            }

            if (strtolower($type) === 'loan') {
                if (strtolower($transaction) == 'credit') {
                    $data[$empId]['loan_addition'] += $total_amount;
                } else {
                    $data[$empId]['loan_deduction'] += $deducted_amount;
                }
            } else if (strtolower($type) === 'advance') {
                if (strtolower($transaction) == 'credit') {
                    $data[$empId]['advance_addition'] += $total_amount;
                } else {
                    $data[$empId]['advance_deduction'] += $deducted_amount;
                }
            } else {
                if (strtolower($transaction) == 'hold') {
                    $data[$empId]['arrear_addition'] += $total_amount;
                } else {
                    $data[$empId]['arrear_deduction'] += $deducted_amount;
                }
            }
        }
        return $this->response->setJSON(['data' => $data]);
    }

    public function getemployeespayments()
    {
        $db = db_connect();

        $emp_id = $this->request->getGet('emp_id');

        $data = $db->query("SELECT * FROM payments WHERE pay_empid =? ORDER BY created_at DESC", [$emp_id])->getResultArray();

        return $this->response->setJSON($data);
    }

    // Additional employees 
    // ------------------------------------------------
    // 03-07-2025 
    // ------------------------------------------------
    public function addManualUser()
    {
        $db = \Config\Database::connect();
        $emp_id = $this->request->getPost('emp_id');
        $name = $this->request->getPost('name');
        $salary = $this->request->getPost('salary');
        $account = $this->request->getPost('account');
        $ifsc = $this->request->getPost('ifsc');
        $lop = $this->request->getPost('lop');

        // Optional: Add validation
        // if (!$name || !$salary || !$account || !$ifsc || !$lop || !$emp_id) {
        // /   // return $this->response->setJSON(['status' => 'error', 'message' => 'Missing required fields.']);
        //}

        $data = [
            'emp_id' =>  $emp_id,
            'name'       => $name,
            'salary'     => $salary,
            'account_no' => $account,
            'ifsc_code'  => $ifsc,
            'lop_days'  => $lop,
            'is_manual' => 1,
            'dept' => '10',
            'payable_days' => 0,
            'created_at' => date('Y-m-d H:i:s') // 👈 Optional: if your table doesn't auto-fill
        ];

        $builder = $db->table('payrole');

        $inserted = $builder->insert($data);

        if ($inserted) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Insert failed']);
        }
    }

    public function getManualUsers($month, $year)
    {
        // $month = (int) date('m');
        // $year = (int) date('Y');

        $dates = $this->getStartAndEndDate($month, $year);

        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];


        $db = db_connect();
        $payroleModel = $db->table('payrole');
        $manualUsers = $payroleModel->where('DATE(created_at) >=', $startDate)->where('DATE(created_at) <= ', $endDate)->where('is_manual', 1)->get()->getResultArray();

        foreach ($manualUsers as &$user) { // Use reference (&) to update values inside the loop

            $empId = $user['emp_id'] ?? $user['id']; // fallback if 'emp_id' is not available
            $user['additional'] = $this->getAdditionalPay($empId); // total additional amount
            $user['additional_data'] = $this->getAdditionalPayDetails($empId); // array

            $user['deduction'] = $this->getDeductionPay($empId); // total deduction amount
            $user['deduction_data'] = $this->getDeductionPayDetails($empId); // array

            $additional = $this->getAdditionalPay($user['emp_id']);
            $deduction = $this->getDeductionPay($user['emp_id']);

            // $user['netPay'] = $user['salary'] + $additional - $deduction;
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $manualUsers
        ]);
    }


    // public function getManualUsers()
    // {
    //     $manualUserModel = new \App\Models\PayroleUserModel();

    //     // Filter for current month
    //     $users = $manualUserModel->where('MONTH(created_at)', date('m'))
    //         ->where('YEAR(created_at)', date('Y'))
    //         ->findAll();

    //     return $this->response->setJSON($users);
    // }


    public function getemplyeename()
    {
        $employeeModel = new \App\Models\EmployeeModel();

        $employees = $employeeModel
            ->select('emp_id, name')
            ->where('dept', 10) // Assuming 'emp_department' is your column name
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $employees
        ]);
    }

    protected function getAdditionalPay($empId)
    {

        $month = (int) date('m');
        $year = (int) date('Y');

        $dates = $this->getStartAndEndDate($month, $year);

        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        $paymentModel = new \App\Models\PaymentModel();
        $addition = $paymentModel
            ->where('pay_empid', $empId)
            ->where('pay_type != ', 'Arrear')
            ->where('transaction_type', 'Credit')
            ->selectSum('pay_total_amount')
            ->first()['pay_total_amount'] ?? 0;

        $arrerAddition =   $paymentModel
            ->where('pay_empid', $empId)
            ->where('pay_type = ', 'Arrear')
            ->selectSum('deduction_amount')
            ->first()['deduction_amount'] ?? 0;

        return $addition + $arrerAddition;
    }

    protected function getAdditionalPayDetails($empId)
    {

        $month = (int) date('m');
        $year = (int) date('Y');

        $dates = $this->getStartAndEndDate($month, $year);

        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        $paymentModel = new \App\Models\PaymentModel();
        $addition = $paymentModel
            ->select('pay_type, pay_total_amount')
            ->where('pay_empid', $empId)
            ->where('pay_type !=', 'Arrear')
            ->where('pay_date >= ', $startDate)
            ->where('pay_date <= ', $endDate)
            ->where('transaction_type', 'Credit')
            ->findAll();

        $additionArrear = $paymentModel
            ->select('pay_type, deduction_amount')
            ->where('pay_empid', $empId)
            ->where('pay_type', 'Arrear')
            ->where('deducted_date >= ', $startDate)
            ->where('deducted_date <= ', $endDate)
            ->where('transaction_type', 'Credit')
            ->findAll();

        $allAdditions = [];

        // Loop through standard additions (not Arrear)
        foreach ($addition as $item) {
            $allAdditions[] = [
                'pay_type' => $item['pay_type'],
                'amount'   => $item['pay_total_amount'],
            ];
        }

        // Loop through Arrear additions (special case using deduction_amount as amount)
        foreach ($additionArrear as $item) {
            $allAdditions[] = [
                'pay_type' => $item['pay_type'], // will be 'Arrear'
                'amount'   => $item['deduction_amount'],
            ];
        }

        return $allAdditions;
    }

    protected function getDeductionPay($empId)
    {

        $month = (int) date('m');
        $year = (int) date('Y');

        $dates = $this->getStartAndEndDate($month, $year);

        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];


        $paymentModel = new \App\Models\PaymentModel();
        $deduction = $paymentModel
            ->where('pay_empid', $empId)
            ->where('pay_type != ', 'Arrear')
            ->selectSum('deduction_amount')
            ->first()['deduction_amount'] ?? 0;

        $arrerdeduction = $paymentModel
            ->where('pay_empid', $empId)
            ->whereIn('pay_type', ['Arrear', 'Non_Performance'])
            ->selectSum('pay_total_amount')
            ->first()['pay_total_amount'] ?? 0;

        return $deduction + $arrerdeduction;
    }

    public function getDeductionPayDetails($empId)
    {
        $month = (int) date('m');
        $year = (int) date('Y');

        $dates = $this->getStartAndEndDate($month, $year);

        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        // return $this->response->setJSON(['date' => "$startDate - $endDate"]);

        // $startdate = $this->getStartAndEndDate();
        $paymentModel = new \App\Models\PaymentModel();

        $deduction = $paymentModel
            ->select('pay_type, deduction_amount')
            ->where('pay_empid', $empId)
            ->where('pay_type !=', 'Arrear')
            ->where('deducted_date >= ', $startDate)
            ->where('deducted_date <= ', $endDate)
            ->where('transaction_type', 'Debit')
            ->findAll();

        $deductionArrear = $paymentModel
            ->select('pay_type, pay_total_amount')
            ->where('pay_empid', $empId)
            ->whereIn('pay_type', ['Arrear', 'Non_Performance'])
            ->where('pay_date >=', $startDate)
            ->where('pay_date <=', $endDate)
            ->whereIn('transaction_type', ['Hold', 'Debit'])
            ->findAll();


        $allDeductions = [];

        // Loop through standard deductions (not Arrear)
        foreach ($deduction as $item) {
            $allDeductions[] = [
                'pay_type' => $item['pay_type'],
                'amount' => $item['deduction_amount'],
            ];
        }

        // Loop through arrear deductions
        foreach ($deductionArrear as $item) {
            $allDeductions[] = [
                'pay_type' => $item['pay_type'], // will be 'Arrer'
                'amount' => $item['pay_total_amount'],
            ];
        }

        return $allDeductions;
    }

    public function savepayroll()
    {
        $db = db_connect();

        $manmonth = (int) date('m');
        $manyear = (int) date('Y');

        $dates = $this->getStartAndEndDate($manmonth, $manyear);

        $manstartDate = $dates['startDate'];
        $manendDate = $dates['endDate'];



        $incoming = $this->request->getJSON(true); // true = associative array
        $data = $incoming['data'];

        $manual = $data['manual_user_data'];
        $employee = $data['employee_data'];
        $oe_month = (int) $employee['monthdays'];
        $c = 0;
        // return $this->response->setJSON(['data1' => $manual, 'data2' => $employee['data'], 'dates' => $dates]);

        foreach ($manual['data'] as $man) {
            $c++;

            $emp_id = $man['emp_id'];
            $salary = (int)  $man['salary'];
            $deduction = (int)  $man['deduction'];
            $addition = (int)  $man['additional'];
            $netpay = round($salary + $addition - $deduction);

            $exist_manual = $db->query("SELECT * FROM payrole WHERE emp_id = ? AND DATE(created_at) BETWEEN ? AND ? limit 1", [$emp_id, $manstartDate, $manendDate])->getResultArray();
            if (!empty($exist_manual)) {
                $data = [
                    'additional' => $addition,
                    'deduction' => $deduction,
                    'netpay' => $netpay,
                    'is_manual' => 1,
                    'dept' => '10',
                ];
                $db->table('payrole')->set($data)->where(['emp_id' => $emp_id, 'DATE(created_at) >=' => $manstartDate, 'DATE(created_at) <= ' => $manendDate])->update();
            } else {
                $data = [
                    'emp_id' => $emp_id,
                    'name' => $man['name'],
                    'salary' => $salary,
                    'presentDays' => $man['presentDays'],
                    'absentDays' => $man['absentDays'],
                    'compensation' => $man['compensation'],
                    'lop_days' => $man['lop_days'],
                    'sortfall' => $man['sortfall'],
                    'totalOEHours' => $man['totalOEHours'],
                    'actual_present' => $man['actual_present'],
                    'permission_hours' => $man['permission_hours'],
                    'additional' => $addition,
                    'deduction' => $deduction,
                    'netpay' => $netpay,
                    'is_manual' => 1,
                    'dept' => '10',
                    'payable_days' => $oe_month + ((int) $man['compensation']) -  ((int) $man['absentDays']) -  ((int) $man['lop_days']),

                ];

                $db->table('payrole')->insert($data);
            }
        }



        foreach ($employee['data'] as $e) {
            $c++;
            $emp_id = $e['emp_id'];
            $salary = (int)  $e['currecnt_salary'];
            $deduction = (int)  $e['deduction'];
            $addition = (int)  $e['additional'];
            $netpay = round($salary + $addition - $deduction);
            $exist_manual = $db->query("SELECT * FROM payrole WHERE emp_id = ? AND DATE(created_at) BETWEEN ? AND ? limit 1", [$emp_id, $manstartDate, $manendDate])->getResultArray();

            if (!empty($exist_manual)) {
                $data = [
                    'salary' => $salary,
                    'presentDays' => $e['presentDays'],
                    'absentDays' => $e['absentDays'],
                    'compensation' => $e['compensation'],
                    'lop_days' => $e['lop_days'],
                    'sortfall' => $e['sortfall'],
                    'totalOEHours' => $e['totalOEHours'],
                    'actual_present' => $e['actual_present'],
                    'permission_hours' => $e['permission_hours'],
                    'additional' => $addition,
                    'deduction' => $deduction,
                    'netpay' => $netpay,
                    'payable_days' => $oe_month + ((int) $e['compensation']) - ((int) $e['absentDays']) - ((int) $e['lop_days']),
                ];
                $db->table('payrole')->set($data)->where(['emp_id' => $emp_id, 'DATE(created_at) >=' => $manstartDate, 'DATE(created_at) <= ' => $manendDate])->update();
            } else {
                $data = [
                    'emp_id' => $emp_id,
                    'name' => $e['name'],
                    'salary' => $salary,
                    'presentDays' => $e['presentDays'],
                    'absentDays' => $e['absentDays'],
                    'compensation' => $e['compensation'],
                    'lop_days' => $e['lop_days'],
                    'sortfall' => $e['sortfall'],
                    'totalOEHours' => $e['totalOEHours'],
                    'actual_present' => $e['actual_present'],
                    'permission_hours' => $e['permission_hours'],
                    'additional' => $addition,
                    'deduction' => $deduction,
                    'netpay' => $netpay,
                    'is_manual' => 0,
                    'dept' => $e['dept'],
                    'payable_days' => $oe_month + ((int) $e['compensation']) - ((int) $e['absentDays']) - ((int) $e['lop_days']),
                ];

                $db->table('payrole')->insert($data);
            }
        }
        return $this->response->setJSON(['data1' => $manual, 'data2' => $employee, 'dates' => $dates, 'counts' => $c]);
    }
}
