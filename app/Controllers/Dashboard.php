<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\CompanyHolidayModel;
use App\Models\CompensationModel;
use App\Models\EmployeeModel;
use App\Models\LeaveHistoryModel;
use App\Models\LeaveRquestModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\VersionUpdateModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use mysqli;

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

        $datas['attendance'] = $attendanceResult['data'];
        $datas['dates'] = $attendanceResult['dates'];
        $datas['empid'] = $empId;
        $datas['name'] = $name;



        return view('dashboard/index', $datas);
        // // return session()->get('staff_id');
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

        return view('requirments/EmployeeAttendance', $datas);
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
}
