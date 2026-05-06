<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeanceRequest extends FormRequest
{
    public function authorize(): bool 
    { 
       return true ; 
    }

    public function rules(): array
    {
        return [
            'halaqa_id'    => 'nullable|exists:halaqat,id',
            'date'         => 'required|date_format:Y-m-d',
            'classroom_id' => 'nullable|integer|exists:classrooms,id',
            'notes'        => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'halaqa_id.exists'   => 'الحلقة غير موجودة',
            'date.required'      => 'التاريخ مطلوب',
            'date.date_format'   => 'صيغة التاريخ يجب أن تكون YYYY-MM-DD',
            'notes.max'          => 'الملاحظات لا تتجاوز 500 حرف',
        ];
    }
}