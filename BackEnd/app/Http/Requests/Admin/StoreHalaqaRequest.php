<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHalaqaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'teacher_id'   => 'nullable|integer|exists:teachers,id',
            'name'         => 'required|string|max:100',
            'schedule'     => 'nullable|string|max:255',
            'max_students' => 'nullable|integer|min:1|max:100',
        ];
    }
}