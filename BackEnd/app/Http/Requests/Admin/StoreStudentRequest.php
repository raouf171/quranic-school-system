<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    // authorize() = qui peut utiliser cet endpoint
    // true = on laisse le middleware gérer l'autorisation
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // parent_id doit exister dans la table parents
            'parent_id'       => 'required|integer|exists:parents,id',

            // halaqa optionnelle — assignée plus tard
            'halaqa_id'       => 'nullable|integer|exists:halaqat,id',

            'full_name'       => 'required|string|max:100',
            'birth_date'      => 'nullable|date|before:today',

            // doit être une des valeurs ENUM définies
            'social_state'    => 'nullable|in:normal,father_deceased,mother_deceased,divorced_parents',
            'fee_status'      => 'nullable|in:paid,pending,late,exempt',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.required' => 'يجب تحديد ولي الأمر',
            'parent_id.exists'   => 'ولي الأمر غير موجود',
            'full_name.required' => 'الاسم الكامل مطلوب',
            'halaqa_id.exists'   => 'الحلقة غير موجودة',
        ];
    }
}