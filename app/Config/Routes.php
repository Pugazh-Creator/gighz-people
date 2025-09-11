<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$role = session()->get('role');
if($role != ''){
    $routes->setAutoRoute(true);   
}


// / for the index of our web application
$routes->get('/', 'Auth::index');
$routes->get('attendance/getPunchRecords', 'BiometricController::getPunchRecords');

// $routes->get('auth/registerUser', 'Auth::registerUser');
$routes->post('/loginUser', 'Auth::loginUser'); // it cheking user credential for entering into daashboard
// $routes->get('funtest', 'Auth::send_otp');

$routes->get('biometric/check', 'Dashboard::checkDevice');
$routes->get('biometric', 'Dashboard::biodevice');
$routes->get('auth/auto_login', 'Auth::auto_login');

/**
 * Adding Version Details
 */
    $routes->get('add-gighz-version-details','Dashboard::version');
    $routes->post('/add-version-details', 'Dashboard::addVersionDetails');

/**
 * Attendance
 */
$routes->get('attendance/get_punches', 'BiometricController::getPunchRecords');
$routes->post('attendance/deletePunch', 'BiometricController::delete_punch');
$routes->post('attendance/addManualPunch', 'BiometricController::addManualPunch');
$routes->get('calculate', 'BiometricController::recalculatePunchTime');
$routes->get('/attendance', 'BiometricController::index');
$routes->get('/employee-attendance', 'Dashboard::getEmployeesAttendance');
$routes->get('attendance/loadUpdatedData', 'BiometricController::loadUpdatedData');
$routes->post('attendance/changeWorkType', 'BiometricController::chanegTypeOfWork');


/**
 * Leaves 
 */
$routes->get('employeesAttendanceDetails', 'Dashboard::getEmployeesAttendance');

// it cheking user credential for entering into daashboard
$routes->post('/reset-password', 'Auth::resetPassword');
$routes->get('/reset-password', 'Auth::resetPassword');
$routes->get('/goResetPassword', 'Auth::goResetPassword');
//  $routes->get('/goResetPassword', 'Auth::goResetPasswordOTP');
$routes->post('/send_otp', 'Auth::send_otp');
$routes->post('verify-otp', 'Auth::verifyOtp');



$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('auth/register', 'Auth::register'); // its redirect to the register function the go to register.php file
    $routes->post('auth/registerUser', 'Auth::registerUser'); // its execuit the registerUser Function & storing user details on database
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('auth/logout', 'Auth::logout');
    $routes->get('dashboard/hr', 'HRController::index');
    // $routes->get('/companyHoliday', 'HRController::fetchHollidays');
    $routes->post('insert_holidays', 'HRController::addAndFetchHoliday');
    $routes->get('/leaveRquest', 'HRController::leaveRequest');
    $routes->get('/hr/change-status/(:num)/(:segment)/(:segment)/(:num)', 'HRController::change_status/$1/$2/$3/$4');
    $routes->post('/hr/change-status/(:num)/(:segment)/(:segment)/(:num)', 'HRController::change_status/$1/$2/$3/$4');
    // $routes->get('/hr/change-status/(:num)/(:alpha)/(:alpha)/(:num)', 'HRController::change_status/$1/$2/$3/$4');
    // $routes->get('/hr/change-status/(:num)/(:alpha)/(:num)/(:num)', 'HRController::change_status/$1/$2/$3/$4');
    $routes->get('dashboard/applyLeave', 'EmployeeController::applyLeave');
    $routes->post('/leaveapplysubmit', 'EmployeeController::leaveApplySubmit');
    $routes->get('/hr/check_new_leave_requests', 'HRController::check_new_leave_requests');
    $routes->get('leaveRequests2', 'HRController::ShowingLeaveRequests');
    $routes->get('leave-request', 'HRController::ShowingLeaveRequests');
    $routes->get('empleaves', 'EmployeeController::getMyLaeves');
    $routes->get('empleaves', 'EmployeeController::availabelLeaves');
    $routes->post('leave/updateLeave', 'HRController::updateLeave');
    $routes->get('leave/updateLeave', 'HRController::updateLeave');
    $routes->get('/update-employee-leave', 'HRController::updateEmployeeLeaves');
    /**
     * Company Holidays
     */
    $routes->get('/companyHoliday', 'HRController::companyHoliday');
    $routes->post('holidays/updateHoliday', 'HRController::updateHoliday');
    $routes->get('deleteHoliday/(:num)', 'HRController::deleteHoliday/$1');

    /**
     * Compensation 
     */
    $routes->get('compen-request', 'CompensationController::showAllCompensation');
    $routes->post('/compensation-request', 'CompensationController::applyCompensation');
    $routes->get('/showCompen', 'CompensationController::showAllCompensation');
    $routes->get('/compensation', 'CompensationController::getMycompensation');
    $routes->get('/update-compen-status/(:num)/(:segment)/(:segment)/(:num)', 'CompensationController::changeCompenStatus/$1/$2/$3/$4');

    /**
     * Attendance Logs
     */
    $routes->get('attendance/getPunchRecords', 'BiometricController::getPunchRecords');

    $routes->post('attendance/addManualPunch', 'BiometricController::addManualPunch');
});
