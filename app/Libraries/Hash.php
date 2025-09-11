<?php

namespace App\Libraries;
// use Illuminate\Support\Facades\Hash;

class Hash
{
    // encrypt password

    public static function encrypt($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // cheking db password & user entered password
    public static function check($userPassword, $dbUserPassword)
    {
       return (password_verify($userPassword, $dbUserPassword)) ;
    }
}
