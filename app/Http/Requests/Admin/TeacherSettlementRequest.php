<?php

namespace App\Http\Requests\Admin;

use App\Models\Distribution;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TeacherSettlementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('settle teacher payments') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => ['required', Rule::exists('teachers', 'id')],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'settlement_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure extra validation.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $teacher = Teacher::query()->find($this->integer('teacher_id'));

            if (! $teacher) {
                return;
            }

            $earned = (float) Distribution::query()
                ->where('teacher_id', $teacher->id)
                ->sum('amount');

            $settled = (float) \App\Models\TeacherSettlementItem::query()
                ->whereHas('distribution', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->sum('amount');

            $outstanding = max(0, $earned - $settled);
            $amount = (float) $this->input('amount');

            if ($outstanding <= 0) {
                $validator->errors()->add('amount', 'This teacher has no outstanding payable right now.');
            }

            if ($amount > $outstanding) {
                $validator->errors()->add('amount', 'Settlement amount cannot exceed outstanding payable of '.number_format($outstanding, 2).'.');
            }
        });
    }
}
