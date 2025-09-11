<?php

if (!function_exists('send_otp')) {
    function send_otp()
    {
      return random_int(100000,999999);
    }
}
