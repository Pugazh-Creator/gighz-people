<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveHistoryModel;
use App\Models\LeaveRquestModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\VersionUpdateModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use CodeIgniter\Controller;
// use CodeIgniter\Database\Database;
use mysqli;
use Config\Database;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Thumbnails;

class Dashboard extends BaseController
{
    public function __construct()
    {
        helper(['url', 'form']);
    }
    public function index()
    {
        $attendancemodel = new AttendanceModel;
        $versionModel = new VersionUpdateModel;

        $empId = session()->get('emp_id');
        $name = session()->get('name');
        $attendanceResult = $attendancemodel->getEmployeesLeaves($empId);
        $datas['versions'] = $versionModel->getAllVersion();
        $datas['thisPage'] = "Dashboard";

        $datas['attendance'] = $attendanceResult['data'];
        $datas['dates'] = $attendanceResult['dates'];
        $datas['empid'] = $empId;
        $datas['name'] = $name;
        $datas["basedata"] = $this->baseDatas();




        echo view('templates/header', $datas);
        echo view('templates/sidebar', $datas);
        echo view('dashboard/index', $datas);
        echo view('templates/footer', $datas);
        // // return session()->get('staff_id');
    }

    // ---------------- GET OE START DATE AND END DATE ------------------

    public function getStartAndEndDate($selectedMonth = null, $selectedYear = null)
    {
        // Check if the user manually selected a month
        $isManualSelection = !is_null($selectedMonth) && !is_null($selectedYear);

        $dates = (int) date('m');
        // $dates = $date - 1 === 0 ? 12 : $date - 1;

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

        return $data;
        // return $this->response->setJSON($data);
    }


    public function dashboardDatas()
    {
        $db = db_connect();
        $session = session();
        $empid = "GZ44";
        // $empid = $session->get('emp_id');
        $attendancemodel = new AttendanceModel;
        $versionModel = new VersionUpdateModel;
        $getAttendanceID = $db->query('SELECT user_id from attendance_users where emp_id = ?', [$empid])->getRowArray();
        $data['Attendance'] = [];

        if (!empty($getAttendanceID)) {

            $userID = $getAttendanceID['user_id'];

            $data['Attendance'] = $attendancemodel->getEmployeesLeaves($empid)['data'][$userID];



            $data['records'] =  end($attendancemodel->getEmployeesLeaves($empid)['data'][$userID]['records'])['total'];
        }


        $getDate = $this->getStartAndEndDate();
        $oeStartDate = $getDate['startDate'];
        $oeEndDate = $getDate['endDate'];

        $data['rr'] = $this->getworkedRRAndGeneralWorks($empid, $oeStartDate, $oeEndDate)['R&R'];
        $data['general'] = $this->getworkedRRAndGeneralWorks($empid, $oeStartDate, $oeEndDate)['General'];

        $data['versions'] = $versionModel->getAllVersion();

        $data['start'] = $oeStartDate;
        $data['end'] = $oeEndDate;

        $data['leave'] = $db->query(
            " SELECT * 
                        FROM leave_request 
                        WHERE emp_id = ? and DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
                        ORDER BY created_at DESC 
                        LIMIT 1",
            [$empid]
        )->getRowArray();

        $data['compensation'] = $db->query(
            " SELECT * 
                        FROM compensation_request 
                        WHERE emp_id = ? and DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
                        ORDER BY created_at DESC 
                        LIMIT 1",
            [$empid]
        )->getRowArray();

        $data['Permission'] = $db->query(
            " SELECT * 
                        FROM permission_hrs 
                        WHERE permission_user_id = ? and DATE(permission_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
                        ORDER BY permission_created DESC 
                        LIMIT 1",
            [$empid]
        )->getRowArray();

        return $this->response->setJSON($data);
    }

    public function sendattendancedatatotimesheet($empID)
    {
        $attendancemodel = new AttendanceModel;

        $employeeModel = new EmployeeModel;
        $employee_id = $employeeModel->where('no', $empID)->first();
        $staffId = $employee_id['emp_id'];

        $attendanceResult = $attendancemodel->getEmployeesLeaves($staffId);
        $datas['attendance'] = $attendanceResult['data'];
        $datas['dates'] = $attendanceResult['dates'];

        return $this->response->setJSON($datas);
    }

    public function getEmployeesAttendance()
    {
        $attendancemodel = new AttendanceModel;

        $datas["basedata"] = $this->baseDatas();
        $datas['thisPage'] = 'Attendance';

        $empId = session()->get('emp_id');
        $selectedMonth = $this->request->getGet('month');
        $selectedYear = $this->request->getGet('year');

        // // Generate list of months for dropdown
        $datas['months'] = [
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
        $datas['years'] = range($currentYear - 5, $currentYear + 2);

        // Set default selected values

        if (date('d') > 24) {
            $month = (date('m') == 12) ? 1 : date('m') + 1;
            $year = (date('m') == 12) ? date('Y') + 1 : date('Y');
        } else {
            $month = date('m');
            $year = date('Y');
        }

        $datas['selectedMonth'] = $selectedMonth ?? $month;
        $datas['selectedYear'] = $selectedYear ?? $year;

        $attendanceResult = $attendancemodel->getEmployeesLeaves($empId, $selectedMonth, $selectedYear);


        $datas['attendance'] = $attendanceResult['data'];
        $datas['dates'] = $attendanceResult['dates'];


        echo view('templates/header', $datas);
        echo view('templates/sidebar', $datas);
        echo view('requirments/EmployeeAttendance', $datas);
        echo view('templates/footer', $datas);
    }

    public function version()
    {
        return view('dashboard/versions');
    }

    public function addVersionDetails()
    {

        $versionModel = new VersionUpdateModel;
        $validated = $this->validate([
            'version' => [
                'rules' => 'required',
                'errors' => ['required' => 'Version is Not Entered.']
            ],
            'detailes' => [
                'rules' => 'required',
                'errors' => ['required' => 'You Should Enter the Version Details.']
            ],
            'lanch_date' => [
                'rules' => 'required',
                'errors' => ['required' => 'Enter Lanched Date.']
            ],
            'visible' => [
                'rules' => 'required',
                'errors' => ['required' => 'Set Version Visibility.']
            ]
        ]);

        if (!$validated) {
            return view('dashboard/versions', ['validation' => $this->validator]);
        }

        $version = $this->request->getPost('version');
        $details = $this->request->getPost('detailes');
        $lanchDate = $this->request->getPost('lanch_date');
        $status = $this->request->getPost('status');
        $visible = $this->request->getPost('visible');

        $data = [
            'version' => $version,
            'version_details' => $details,
            'approved_status' => $status,
            'lanched_date' => $lanchDate,
            'visible_level' => $visible
        ];

        if ($versionModel->save($data)) {
            return redirect()->back()->with('success', 'Version Added Successfully.');
        } else {
            return redirect()->back()->with('error', 'Fail to Add Version.');
        }
    }

    public function biodevice()
    {
        return view('test');
    }

    public function checkDevice()
    {
        $output = shell_exec('node C:\xampp\htdocs\gighz\public\asset\js\biometric.js');

        // return view('test',['message' => $output]);
        // You can check the response message or exit code
        if (strpos($output, 'Device is connected') !== false) {
            return $this->response->setBody('success');
        } else {
            return $this->response->setBody('fail');
        }
    }

    // Dinomic version change
    function getAppVersion()
    {
        $model = new VersionUpdateModel();
        $latest = $model->orderBy('id', 'desc')->first();
        $version = $latest['version'];
        $cleanVersion = substr($version, 1);
        return $cleanVersion; // Or sanitize remove v 
    }


    public function getAppVersions()
    {
        $version = $this->getAppVersion();
        return $version;
    }

    function dynamicVersion()
    {
        $userversion = $this->request->getGet("selected");
        // return $this->response->setJSON(['status' => $userversion]);


        $model = new VersionUpdateModel();
        $latest = $model->orderBy('id', 'desc')->first();
        $version = $latest['version'];

        $cleanVersion = [substr($version, 1)];

        $parts = explode('.', $cleanVersion[0]);

        $parts = array_pad($parts, 3, 0);

        if ($userversion == 'max') {
            $parts[0]++;
            $parts[1] = 0;
            $parts[2] = 0;
        }
        if ($userversion == 'min') {
            $parts[1]++;
            $parts[2] = 0;
        }
        if ($userversion == 'inmin') {
            $parts[2]++;
        }
        $finalversion = 'v' . $parts[0] . '.' . $parts[1] . '.' . $parts[2];
        return $this->response->setJSON(['version' => $finalversion]); // Or sanitize as needed
    }

    public function baseDatas()
    {
        $session = session();
        $data['name'] = $session->get('name');
        $data['role'] = $session->get('role');
        $data['emp_id'] = $session->get('emp_id');

        $db = db_connect();
        $query_role = $db->query("SELECT user_name FROM `role` WHERE id='" . $data['role'] . "'");
        $data['emp_role'] = $query_role->getResultArray();

        $query = $db->query("SELECT * FROM tbl_user_permission WHERE user_category='" . $data['emp_role'][0]['user_name'] . "'");
        $data['emp_info'] = $query->getResultArray();

        $data['employee'] = $db->query("SELECT * FROM employees where emp_id = ? limit 1", ['GZ44'])->getRowArray();
        $data['image'] = $data['employee']['image'] ?? null;

        $data["version"] = $this->getAppVersions();


        // return $this->response->setJSON($data);
        return $data;
    }

    public function testing()
    {
        // $data["basedata"] = $this->baseDatas();
        $data['test'] = 'magendiran';

        // $attendanceResult = $attendancemodel->getEmployeesLeaves($empId);
        $attendancemodel = new AttendanceModel;
        $data['date'] = $attendancemodel->getEmployeesLeaves('gz44');

        return $this->response->setJSON($data);
    }

    // GETTING r&r AND gENERAL hOURS
    public function getworkedRRAndGeneralWorks($empID, $oeStartDate, $oeEndDate)
    {
        $remoteDB = Database::Connect('hostinger');


        $query = $remoteDB->query(
            "SELECT * FROM tbl_timesheet 
         WHERE timesheet_user_id = ? 
         AND timesheet_date BETWEEN ? AND ?",
            [$empID, $oeStartDate, $oeEndDate]
        )->getResultArray();

        // --------------------------------------
        $totalGeneral = 0;
        $totalRR = 0;

        foreach ($query as $row) {
            list($h, $m) = explode(":", $row["timesheet_working_time"]);
            $seconds = ($h * 3600) + ($m * 60);

            if (strtoupper($row["timesheet_project_category"]) === "2") {
                $totalGeneral += $seconds;
            } elseif (strtoupper($row["timesheet_project_category"]) === "1") {
                $totalRR += $seconds;
            }
        }

        // Format time helper
        $formatTime = function ($seconds) {
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            return sprintf("%02d:%02d", $h, $m);
        };

        $result = [
            "General" => $formatTime($totalGeneral),
            "R&R"     => $formatTime($totalRR)
        ];
        // -------------------------------------

        return $result; // return the result as array
    }
}
