<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemorizationRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true;  // RoleMiddleware handles teacher role
    }

    public function rules(): array
    {
        return [
            'seance_id'      => 'nullable|integer|exists:seances,id',  
            'student_id'     => 'required|integer|exists:students,id',
            'evaluation_id'  => 'required|integer|exists:evaluations,id',
            'surah_start'    => 'required|integer|min:1|max:114',
            'verse_start'    => 'required|integer|min:1|max:286',
            'surah_end'      => 'required|integer|min:1|max:114',
            'verse_end'      => 'required|integer|min:1|max:286',
            'points'         => 'nullable|integer|min:0|max:20',  
        ];
    }

    public function messages(): array
    {
        return [
            'seance_id.required'     => 'رقم الجلسة مطلوب',
            'seance_id.exists'       => 'الجلسة غير موجودة',
            'student_id.required'    => 'يجب تحديد الطالب',
            'student_id.exists'      => 'الطالب غير موجود',
            'evaluation_id.required' => 'يجب تحديد التقييم',
            'evaluation_id.exists'   => 'التقييم غير موجود',
            'surah_start.required'   => 'يجب تحديد بداية السورة',
            'verse_start.required'   => 'يجب تحديد بداية الآية',
            'surah_end.required'     => 'يجب تحديد نهاية السورة',
            'verse_end.required'     => 'يجب تحديد نهاية الآية',
        ];
    }
}