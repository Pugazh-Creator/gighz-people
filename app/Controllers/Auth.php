<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Hash;
use App\Models\EmployeeModel;
use App\Models\OtpModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use PhpOffice\PhpWord\Shared\Microsoft\PasswordEncoder;
use ReturnTypeWillChange;

class Auth extends BaseController
{
    // enabeling feature
    public function __construct()
    {
        helper(['url', 'form']);
    }

    public function index()
    {
        $load = new Dashboard;
        $version = $load->getAppVersion();
        session()->set('version', $version);
        return view('auth/login');
    }
    public function register()
    {
        return view('auth/register');
        // echo "register User";
    }

    public function registerUser()
    {
        $validated = $this->validate([
            'emp_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'your Employee ID is Required',
                ]
            ],

            'username' => [
                'rule' => 'required',
                'errors' => ['required' => 'Employee name is required']
            ],
            'role' => [
                'rule' => 'required',
                'errors' => ['required' => 'Employee name is required']
            ],
            'password' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'your Password is Required',
                ]
            ],
            'corn_password' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'your Password is Required',
                    'matches'  => 'Password and conform password not match'
                ]
            ],
        ]);

        if (!$validated) {
            return view('auth\register', ['validation' => $this->validator]);
        }
        // save user in Database
        $emp_id = $this->request->getPost('emp_id');
        $name = $this->request->getPost('username');
        $role = $this->request->getPost('role');
        $password = $this->request->getPost('password');

        // echo $emp_id." ".$name.' '.$role." ".$password;

        $roleModel = new RoleModel();
        $roles = $roleModel->find($role);
        $id = $roles['id'];

        $data = [
            'name' => $name,
            'password' => $password,
            'emp_id' => $emp_id,
            'role' => $id
        ];
        $userModel = new UserModel();
        if (!empty($userModel->find($emp_id))) {
            return redirect()->back()->with('fail', 'Employee Id is already present');
        } else {
            $result = $userModel->insert($data);
            if ($result) {
                return redirect()->back()->with('success', 'New User Added Successfully.');
            }
        }


        return  redirect()->back()->with('fail', 'Fail to add User.');

        // // echo "user method is working";
    }

    public function auto_login()
    {
        $isLoggedIn = $this->request->getGet('isLoggedIn'); // get from URL
        $username = $this->request->getGet('username');
        $emp_id = $this->request->getGet('userid');

        if ($isLoggedIn && $username) {
            // Set session or auto-login
            $empModel = new EmployeeModel;
            $employee = $empModel->where('emp_id', $emp_id)->first();

            session()->set([
                'emp_id' => $employee['emp_id'],
                'name' => $employee['name'],
                'role' => $employee['role'],
                'logged_in' => true
            ]);
            return redirect()->to('/dashboard');
        } else {
            // Show normal login page
            return view('/');
        }
    }


    public function loginUser()
    {

        // checking user creantial
        $emp_id = $this->request->getPost('emp_id');
        $password = $this->request->getPost('password');


        $userModel = new UserModel();

        if ($userModel->where('emp_id', $emp_id)->first()) {
            $userInfo = $userModel->where('emp_id', $emp_id)->first();


            // if (!$userInfo) {
            //     echo "User not found!";
            //     return;
            // }

            // echo "Database Password: " . $userInfo['password'];

            // $checkPassword = $password == $userInfo['password'] ? true : false;

            $checkPassword = password_verify($password, $userInfo['password']);

            // $checkPassword = Hash::check('12345',' $2y$10$sUGJ845kT8NO0j/mGTgAae1w2yUQ5Z2ZiJCHl8');


            if (!$checkPassword) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid Password']);
            } else {
                $empModel = new EmployeeModel;
                $employee = $empModel->where('emp_id', $emp_id)->first();

                session()->set([
                    'emp_id' => $employee['emp_id'],
                    'name' => $employee['name'],
                    'role' => $employee['role'],
                    'logged_in' => true
                ]);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Login success']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid GigHz ID']);
        }
    }

    public function logout()
    {
        if (session()->has('logged_in')) {
            session()->destroy();
        }
        return redirect()->to('/')->with('fail', 'Logged Out successfully.');
    }

    public function goResetPasswordOTP()
    {
        return view('auth/sendPaaResetOTP');
    }
    public function goResetPassword()
    {
        return view('auth/resetPassword');
    }

    public function funTest()
    {
        $email = $this->request->getPost('email');
        $to = $email;
        $subject = 'test function 2';
        $message = 'this is a test mail for reset password 2';
        if (password_email($to, $subject, $message)) {
            return $this->response->setJSON(['status' => 'success']);
        }
        return $this->response->setJSON(['status' => 'fail']);
    }

    public function send_otp()
    {
        $employeeModel = new EmployeeModel;
        $otpModel = new OtpModel;
        $email = $this->request->getPost('mail');
        $otp = random_int(100000, 999999);
        $expiry = time();
        $query = $otpModel->where('email', $email)->first();

        // echo $querys['name'];
        if ($employeeModel->where('official_mail', $email)->first()) {

            if (!$query) {
                $otpModel->save([
                    'email' => $email,
                    'otp' => $otp,
                    'expiry' => $expiry
                ]);
            } else {
                $data = [
                    'otp' => $otp,
                    'expiry' => $expiry
                ];
                $otpModel->update($query['id'], $data);
            }

            // Send OTP Email
            // $to = 'rahox85672@knilok.com';
            $to = $email;
            $subject = "OTP for Password Reset";
            $message = "<p>Your OTP is</p><br/>
                   <div style='width:100px; height:50px; background:#f7ccd3; display: flex;align-items: center;
    justify-content:center;'><p style ='color:#da2442; font-size:20px; letter-spacing:1px; border-radius:10px;'>{$otp}</p></div>";
            if (password_email($to, $subject, $message)) {
                return $this->response->setJSON(['status' => 'success', 'massage' => 'Mail Sended']);
            }
        }
        return $this->response->setJSON(['status' => 'error', 'massage' => 'Invalid Mail']);
    }

    public function verifyOtp()
    {
        $email = $this->request->getPost('mail');
        $userotp = $this->request->getPost('otp');

        $otpModel = new OtpModel;

        if ($otpModel->where('email', $email)->first()) {
            $getemail = $otpModel->where('email', $email)->first();
            // $otp = 123456;
            $otp = $getemail['otp'];
            $expiry = $getemail['expiry'];

            if (time() - $expiry > 120) {
                return $this->response->setJSON(['status' => 'error', 'massage' => 'Expired OTP']);
            }
            if ($otp === $userotp) {
                return $this->response->setJSON(['status' => 'success', 'massage' => 'Success']);
                // echo 'matched';
            }
            return $this->response->setJSON(['status' => 'error', 'massage' => 'Invalid OTP']);
            // else{
            //     echo 'not matched';
            // }
        }
    }

    // this function used to reset the password
    public function resetPassword()
    {
        $employeeModel = new EmployeeModel;
        $usermodel = new UserModel();
        $email = $this->request->getPost('mail');
        $password = $this->request->getPost('password');
        $password = password_hash($password, PASSWORD_DEFAULT);

        if ($employeeModel->where('official_mail', $email)->first()) {
            $employees = $employeeModel->where('official_mail', $email)->first();
            $empID = $employees['emp_id'];
            $usermodel->update($empID, ['password' => $password]);
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error']);
        }
    }



    /**
     * -------------------------------- New Updated --------------------------------------------
     */

    public function getcredential()
    {

        $db = db_connect();

        $emp_id = session()->get('emp_id');

        $query = $db->query("SELECT * FROM user WHERE emp_id = ? LIMIT 1", [$emp_id])->getRowArray();

        $data['emp_id'] = $query['emp_id'];

        $data['password'] = $query['password'];

        return $this->response->setJSON($data);
    }

    public function testing()
    {
        $data['test'] = '$2y$10$b/DnrsQLHXRxU4h6qjeCpeG2J.YDADxR9gLJr5mZGYtww9STJuoSS' === 
                        '$2y$10$b/DnrsQLHXRxU4h6qjeCpeG2J.YDADxR9gLJr5mZGYtww9STJuoSS';
        return $this->response->setJSON($data);
    }
}
