<?php

namespace App\Enums;

use PhpEnum\Enum;

/**
 * @method static self OK
 * @method static self UNKNOWN_ERROR
 *
 * @method static self EXCEPTION_HTTP_NOT_FOUND
 * @method static self EXCEPTION_HTTP_METHOD_NOT_ALLOWED
 * @method static self EXCEPTION_AUTHENTICATION
 * @method static self EXCEPTION_VALIDATION
 * @method static self EXCEPTION_THROTTLE_REQUESTS
 */
class ErrorEnum extends Enum
{
    private const OK = [0, 'ok'];

    private const UNKNOWN_ERROR = [99999, '服务器繁忙，请稍后再试'];

    private const EXCEPTION_HTTP_NOT_FOUND = [10001, '接口地址不存在'];
    private const EXCEPTION_HTTP_METHOD_NOT_ALLOWED = [10002, '接口方法不正确'];
    private const EXCEPTION_AUTHENTICATION = [10003, '身份认证失败'];
    private const EXCEPTION_VALIDATION = [10004, '数据验证失败'];
    private const EXCEPTION_THROTTLE_REQUESTS = [10005, '请求过于频繁'];

    private int $code;
    private string $msg;

    protected function construct(int $code, string $msg)
    {
        $this->code = $code;
        $this->msg = $msg;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMsg(): string
    {
        return $this->msg;
    }
}
