<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicAdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'guardian_phone' => ['required', 'string', 'max:20'],
            'school' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'face_capture' => ['nullable', 'string'],
        ];
    }

    /**
     * Add conditional validation for face capture fallback.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('face_capture') && ! $this->hasFile('photo')) {
                $validator->errors()->add('face_capture', 'Use the live face capture or upload a clear fallback photo.');
            }
        });
    }
}
