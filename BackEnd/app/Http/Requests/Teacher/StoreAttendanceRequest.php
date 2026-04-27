<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }

    public function rules(): array
    {
        return [
            // records = tableau de présences
            // ex: [{"student_id":1,"status":"present","points":5}, ...]
            'records'                  => 'required|array|min:1',
            'records.*.seance_id'      => 'nullable|integer|exists:seances,id',  // ← ADD THIS
            'records.*.student_id'     => 'required|integer|exists:students,id',
            'records.*.status'         => 'required|in:present,absent,late,excused',
            'records.*.evaluation_grade'  => 'nullable|string|max:10',
            'records.*.points'         => 'nullable|integer|min:0|max:10',  // ← Changed from evaluation_points
        ];
    }

    public function messages(): array
    {
        return [
            'records.required'                 => 'يجب إرسال سجلات الحضور',
            'records.*.seance_id.required'     => 'رقم الجلسة مطلوب',
            'records.*.seance_id.exists'       => 'الجلسة غير موجودة',
            'records.*.student_id.exists'      => 'طالب غير موجود',
            'records.*.status.in'              => 'حالة الحضور غير صحيحة',
            'records.*.points.min'             => 'النقاط لا يمكن أن تكون سالبة',
            'records.*.points.max'             => 'النقاط لا تتجاوز 10',
        ];
    }
}