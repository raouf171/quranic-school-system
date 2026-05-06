<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'sometimes|required|date_format:Y-m-d',
            'classroom_id' => 'sometimes|nullable|integer|exists:classrooms,id',
            'notes' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'date.date_format' => 'صيغة التاريخ يجب أن تكون YYYY-MM-DD',
            'notes.max' => 'الملاحظات لا تتجاوز 500 حرف',
        ];
    }
}
