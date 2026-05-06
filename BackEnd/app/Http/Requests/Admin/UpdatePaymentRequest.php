<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'    => 'required|in:paid,pending,late,exempt',
            'paid_date' => 'nullable|date',
            'amount'    => 'sometimes|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'الحالة يجب أن تكون: paid, pending, late, أو exempt',
        ];
    }
}