<?php

namespace App\Contracts;

interface AuthService
{
    public function getSmsCode(string $phone_number);
}
