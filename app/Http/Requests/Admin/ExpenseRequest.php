<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage expenses') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['common', 'teacher'])],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->string('type')->toString();
            $teacherId = $this->integer('teacher_id');

            if ($type === 'teacher' && ! $teacherId) {
                $validator->errors()->add('teacher_id', 'Teacher is required for teacher expense.');
            }

            if ($type === 'common' && $teacherId) {
                $validator->errors()->add('teacher_id', 'Teacher should be empty for common expense.');
            }
        });
    }
}
