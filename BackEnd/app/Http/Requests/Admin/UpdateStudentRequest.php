<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // 'sometimes' = valider seulement si le champ est présent
            // Permet les mises à jour partielles (PATCH behavior)
            'parent_id'       => 'sometimes|integer|exists:parents,id',
            'halaqa_id'       => 'sometimes|nullable|integer|exists:halaqat,id',
            'full_name'       => 'sometimes|string|max:100',
            'birth_date'      => 'sometimes|nullable|date',
            'social_state'    => 'sometimes|in:normal,father_deceased,mother_deceased,divorced_parents',
            'fee_status'      => 'sometimes|in:paid,pending,late,exempt',
        ];
    }
}