<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class TestController extends Controller
{
    public function index()
    {
        $db = Database::connect();
        $result = $db->query("select * from employees");
        foreach($result->getResult() as $employees)
        {
           echo $employees->emp_id."<br>";
           echo $employees->name."<br>";
           echo $employees->department."<br>";
           echo $employees->role."<br>";
           echo $employees->designation."<br>";
        }
    }
}
