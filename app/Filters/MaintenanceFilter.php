<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get the client's IP address
        $userIP = $request->getIPAddress();

        // Log the IP for debugging
        log_message('debug', 'User IP: ' . $userIP);

        // Allowed IPs that can bypass maintenance mode
        $allowedIPs = ['127.0.0.1', '::1', '192.168.0.29', '192.168.0.35']; // Replace with your actual IP

        // If the user's IP is in the allowed list, bypass maintenance mode
        if (in_array($userIP, $allowedIPs)) {
            return;
        }

        // If maintenance flag exists, show maintenance page
        if (file_exists(WRITEPATH . 'maintenance.flag')) {
            echo view('maintenance'); // Show maintenance page
            exit();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
