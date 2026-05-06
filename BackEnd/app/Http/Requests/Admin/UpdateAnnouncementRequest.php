<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'          => 'sometimes|string|max:200',
            'content'        => 'sometimes|string',
            'target_roles'   => 'sometimes|array|min:1',
            'target_roles.*' => 'in:all,admin,teacher,parent',
            'expiry_date'    => 'sometimes|nullable|date',
        ];
    }
}