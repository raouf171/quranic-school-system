<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'sometimes|string|max:100',
            'occupation' => 'sometimes|nullable|string|max:255',
            'address'    => 'sometimes|nullable|string',
            'phone'      => 'sometimes|nullable|string|max:30',
        ];
    }
}
