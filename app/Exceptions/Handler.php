<?php

namespace App\Exceptions;

use App\Enums\ErrorEnum;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            throw new ApiException(ErrorEnum::EXCEPTION_HTTP_NOT_FOUND());
        });
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            throw new ApiException(ErrorEnum::EXCEPTION_HTTP_METHOD_NOT_ALLOWED());
        });
        $this->renderable(function (ThrottleRequestsException $e, $request) {
            throw new ApiException(ErrorEnum::EXCEPTION_THROTTLE_REQUESTS());
        });
        $this->renderable(function (AuthenticationException $e, $request) {
            throw new ApiException(ErrorEnum::EXCEPTION_AUTHENTICATION());
        });
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            throw new ApiException(ErrorEnum::EXCEPTION_ACCESS_DENIED());
        });
        $this->renderable(function (ValidationException $e, $request) {
            throw new ApiException(ErrorEnum::EXCEPTION_VALIDATION(), $e->validator->errors());
        });
        $this->renderable(function (Exception $e, $request) {
            throw new ApiException(ErrorEnum::UNKNOWN_ERROR());
        });
    }
}
