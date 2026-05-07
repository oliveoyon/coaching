<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Teacher') || $this->user()?->can('manage attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['present', 'absent', 'late', 'excused', 'pending'])],
            'method' => ['required', Rule::in(['manual', 'qr', 'face'])],
            'note' => ['nullable', 'string', 'max:255'],
            'confidence_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
