<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AuthService implements \App\Contracts\AuthService
{
    public function getSmsCode(string $phone_number)
    {
        $sms_code = (string) rand(100000,999999);
        Cache::put('sms_code_' . $phone_number, $sms_code, 300000);
        return $sms_code;
    }
}
