<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:50',
            'building'     => 'sometimes|nullable|string|max:100',
            'capacity'     => 'sometimes|integer|min:1|max:200',
            'is_available' => 'sometimes|boolean',
        ];
    }
}