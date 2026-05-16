<style>
    .expense-form .section-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .expense-form .section-head {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1rem;
    }
</style>

<div class="expense-form">
<div class="section-card p-4 p-lg-4 mb-4">
    <div class="section-head">Expense Details</div>

    <div class="row g-4">
        <div class="col-md-4">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="common" @selected(old('type', $expense->type ?? 'common') === 'common')>Common Expense</option>
                <option value="teacher" @selected(old('type', $expense->type ?? '') === 'teacher')>Teacher Expense</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="teacher_id" class="form-label">Teacher</label>
            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror">
                <option value="">Select teacher</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $expense->teacher_id ?? '') === (string) $teacher->id)>
                        {{ $teacher->user?->name }}
                    </option>
                @endforeach
            </select>
            @error('teacher_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount', isset($expense) ? number_format((float) $expense->amount, 2, '.', '') : '') }}" class="form-control @error('amount') is-invalid @enderror" required>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="expense_date" class="form-label">Date</label>
            <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control @error('expense_date') is-invalid @enderror" required>
            @error('expense_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="note" class="form-label">Note</label>
            <textarea name="note" id="note" rows="4" class="form-control @error('note') is-invalid @enderror" placeholder="Write a short note">{{ old('note', $expense->note ?? '') }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ isset($expense) ? 'Update Expense' : 'Save Expense' }}</button>
</div>
</div>

@push('scripts')
    <script>
        (() => {
            const typeSelect = document.getElementById('type');
            const teacherSelect = document.getElementById('teacher_id');

            if (!typeSelect || !teacherSelect) {
                return;
            }

            const syncTeacherField = () => {
                const isTeacherExpense = typeSelect.value === 'teacher';
                teacherSelect.disabled = !isTeacherExpense;

                if (!isTeacherExpense) {
                    teacherSelect.value = '';
                }
            };

            syncTeacherField();
            typeSelect.addEventListener('change', syncTeacherField);
        })();
    </script>
@endpush
