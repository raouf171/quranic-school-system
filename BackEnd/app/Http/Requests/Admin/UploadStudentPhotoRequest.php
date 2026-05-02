<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadStudentPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image'    => 'الملف يجب أن يكون صورة',
            'photo.max'      => 'حجم الصورة يجب ألا يتجاوز 4 ميغابايت',
        ];
    }
}
