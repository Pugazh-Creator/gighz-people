<?php

use CodeIgniter\Commands\Utilities\Publish;

if (!function_exists('send_email')) {
    function send_email($to, $subject, $message)
    {
        $email = \Config\Services::email();
        $email->setTo($to);
        // $email->setCC('xamaca1826@gufutu.com');
        $email->setFrom('itsupport@gighz.net', 'GigHz');
        $email->setSubject($subject);
        $email->setMessage($message);

        if ($email->send()) {
            return true;
        } else {
            return $email->printDebugger(['headers']);
        }
    }
}

if (!function_exists('password_email')) {
    function password_email($to, $subject, $message)
    {
        $email = \Config\Services::email();
        $email->setTo($to);
        // $email->setCC('xamaca1826@gufutu.com');
        $email->setFrom('itsupport@gighz.net', 'GigHz');
        $email->setSubject($subject);
        $email->setMessage($message);

        if ($email->send()) {
            return true;
        } else {
            return $email->printDebugger(['headers']);
        }
    }
}

if (!function_exists('payslip')) {
    function payslip($to, $subject, $message, $attached_file)
    {
        $email = \Config\Services::email();
        $email->clear(true);


        $email->setTo($to);
        // $email->setCC('xamaca1826@gufutu.com');
        $email->setFrom('itsupport@gighz.net', 'GigHz');
        $email->setSubject($subject);
        $email->setMessage($message);
        // $email->attach($message);

        // Attach the file if it exists
        if (file_exists($attached_file)) {
            $email->attach($attached_file);
        } else {
            log_message('error', 'Attachment file not found: ' . $attached_file);
        }

        if ($email->send()) {
            return true;
        } else {
            return $email->printDebugger(['headers']);
        }
    }
}
