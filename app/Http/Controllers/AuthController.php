<?php

namespace App\Http\Controllers;

use App\Contracts\AuthService;
use App\Http\Requests\GetSmsCodeRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\JsonResponse;

class AuthController extends Controller
{
    /** @var AuthService $authService */
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function getSmsCode(GetSmsCodeRequest $request)
    {
        $phoneNumber = $request->input('phone_number');
        $smsCode = $this->authService->getSmsCode($phoneNumber);
        return new JsonResponse($smsCode);
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        return new JsonResponse($user);
    }
}
