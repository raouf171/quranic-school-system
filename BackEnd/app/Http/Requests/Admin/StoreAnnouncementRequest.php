<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:200',
            'content'      => 'required|string',
            'target_roles' => 'required|array|min:1',
            'target_roles.*' => 'in:all,admin,teacher,parent',
            'expiry_date'  => 'nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'عنوان الإعلان مطلوب',
            'content.required'        => 'محتوى الإعلان مطلوب',
            'target_roles.required'   => 'يجب تحديد الجمهور المستهدف',
            'target_roles.*.in'       => 'قيمة غير صحيحة في الأدوار المستهدفة',
            'expiry_date.after'       => 'تاريخ الانتهاء يجب أن يكون في المستقبل',
        ];
    }
}