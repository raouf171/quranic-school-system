<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:students,id',
            'month'      => 'required|string|regex:/^\d{4}-\d{2}$/',
            'amount'     => 'required|numeric|min:0',
            'due_date'   => 'required|date',
            'status'     => 'nullable|in:paid,pending,late,exempt',
        ];
    }

    public function messages(): array
    {
        return [
            'month.regex'        => 'Format du mois invalide — utiliser YYYY-MM',
            'student_id.exists'  => 'الطالب غير موجود',
            'amount.required'    => 'المبلغ مطلوب',
        ];
    }
}