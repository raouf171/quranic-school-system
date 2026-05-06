<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    // true = le middleware role:admin gère l'autorisation
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:accounts,email',
            'password'    => 'required|string|min:8',
            'hiring_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'     => 'هذا البريد الإلكتروني مستخدم بالفعل',
            'name.required'    => 'الاسم مطلوب',
            'email.required'   => 'البريد الإلكتروني مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min'     => 'كلمة المرور يجب ألا تقل عن 8 أحرف',
            'hiring_date.date' => 'تاريخ التعيين غير صالح',
        ];
    }
}