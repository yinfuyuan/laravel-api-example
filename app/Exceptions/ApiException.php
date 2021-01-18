<?php

namespace App\Exceptions;

use App\Enums\ErrorEnum;
use Exception;

class ApiException extends Exception
{

    private $data;

    public function __construct(ErrorEnum $enum, $data = '')
    {
        parent::__construct($enum->getMsg(), $enum->getCode());
        $this->data = $data;
    }

    public function render($request)
    {
        return response([
            'code' => $this->getCode(),
            'msg' => $this->getMessage(),
            'data' => $this->data
        ]);
    }
}
