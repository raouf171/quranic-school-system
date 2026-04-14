<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHalaqaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'teacher_id'   => 'sometimes|nullable|integer|exists:teachers,id',
            'name'         => 'sometimes|string|max:100',
            'schedule'     => 'sometimes|nullable|string|max:255',
            'level'        => 'sometimes|nullable|string|max:50',
            'max_students' => 'sometimes|integer|min:1|max:100',
            'is_active'    => 'sometimes|boolean',
        ];
    }
}