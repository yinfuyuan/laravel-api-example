<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSmsCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone_number' => 'required|phone_number',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'phone_number.required' => '请输入您的手机号码',
            'phone_number.phone_number' => '手机号码格式不正确',
        ];
    }
}
