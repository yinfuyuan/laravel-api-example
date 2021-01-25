<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|alpha_num|max:18|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6|max:18',
            'phone_number' => 'required|phone_number|unique:users',
            'sms_code' => 'required|sms_code:phone_number',
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
            'name.required' => '用户名不能为空',
            'name.alpha_num' => '用户名必须是字母数字字符字符串',
            'name.max' => '用户名超过了最大长度限制',
            'name.unique' => '用户名已存在',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式错误',
            'email.unique' => '邮箱已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码长度不能小于6位',
            'password.max' => '密码长度不能大于18位',
            'password.confirmed' => '请输入确认密码password_confirmation',
            'phone_number.required' => '请输入您的手机号码',
            'phone_number.phone_number' => '手机号码格式不正确',
            'phone_number.unique' => '手机号码已存在',
            'sms_code.required' => '请输入验证码',
            'sms_code.sms_code' => '验证码不正确',
        ];
    }
}
