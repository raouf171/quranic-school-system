<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:students,id',
            'evaluation_id' => 'required|integer|exists:evaluations,id',
            'surah_start' => 'required|integer|min:1|max:114',
            'verse_start' => 'required|integer|min:1|max:286',
            'surah_end' => 'required|integer|min:1|max:114',
            'verse_end' => 'required|integer|min:1|max:286',
            'points' => 'nullable|integer|min:0|max:20',
        ];
    }

    public function messages(): array
    {
        return (new StoreMemorizationRequest)->messages();
    }
}
