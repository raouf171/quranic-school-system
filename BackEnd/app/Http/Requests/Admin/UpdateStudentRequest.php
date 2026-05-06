<?php

namespace App\Http\Requests\Admin;

use App\Models\Halaqa;
use App\Models\Student;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // 'sometimes' = valider seulement si le champ est présent
            // Permet les mises à jour partielles (PATCH behavior)
            'parent_id'             => 'sometimes|integer|exists:parents,id',
            'halaqa_id'             => 'sometimes|nullable|integer|exists:halaqat,id',
            'full_name'             => 'sometimes|string|max:100',
            'gender'                => 'sometimes|in:male,female',
            'relationship_nature'   => 'sometimes|in:mother,father,uncle,aunt,grandfather,grandmother,legal_guardian,other',
            'school_level'          => 'sometimes|in:kindergarten,primary,middle_cem,high_school,university,other',
            'birth_date'            => 'sometimes|nullable|date',
            'social_state'          => 'sometimes|in:normal,father_deceased,mother_deceased,divorced_parents',
            'fee_status'            => 'sometimes|in:paid,pending,late,exempt',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $student = $this->route('student');
            if (! $student instanceof Student) {
                return;
            }
            $halaqaId = $this->input('halaqa_id', $student->halaqa_id);
            $gender = $this->input('gender', $student->gender);
            if (! $halaqaId || ! $gender) {
                return;
            }
            $halaqa = Halaqa::find($halaqaId);
            if ($halaqa && $halaqa->gender !== $gender) {
                $validator->errors()->add(
                    'halaqa_id',
                    'جنس الطالب يجب أن يطابق جنس الحلقة المختارة'
                );
            }
        });
    }
}