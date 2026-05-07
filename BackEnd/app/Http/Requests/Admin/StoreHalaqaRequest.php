<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreHalaqaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'teacher_id'   => 'nullable|integer|exists:teachers,id',
            'name'         => 'required|string|max:100',
            'gender'       => 'required|in:male,female',
            'schedule'     => 'nullable|string|max:255',
            'max_students' => 'nullable|integer|min:1|max:100',
            'schedules' => 'nullable|array|min:1',
            'schedules.*.weekday' => 'required_with:schedules|integer|between:0,6',
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.classroom_id' => 'nullable|integer|exists:classrooms,id',
            'schedules.*.is_active' => 'sometimes|boolean',
            'schedules.*.position' => 'sometimes|integer|min:0',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $schedules = $this->input('schedules', []);

            foreach ($schedules as $index => $slot) {
                $start = $slot['start_time'] ?? null;
                $end = $slot['end_time'] ?? null;

                if (! is_string($start) || ! is_string($end)) {
                    continue;
                }

                if ($end <= $start) {
                    $validator->errors()->add(
                        "schedules.$index.end_time",
                        'The end time must be after the start time.'
                    );
                }
            }
        });
    }
}