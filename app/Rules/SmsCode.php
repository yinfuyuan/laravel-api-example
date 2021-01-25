<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Validator;

class SmsCode implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  Validator|null $validator
     * @return bool
     */
    public function passes($attribute, $value, $parameters = array(), $validator = null)
    {
        if(empty($parameters)) {
            return false;
        }
        $phone_number_name = reset($parameters);
        if(empty($phone_number_name)) {
            return false;
        }
        $request = Request::capture();
        $phone_number = $request->only($phone_number_name);
        if(empty($phone_number)) {
            return false;
        }
        $phone_number = reset($phone_number);
        if(Cache::get('sms_code_' . $phone_number) !== $value) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
