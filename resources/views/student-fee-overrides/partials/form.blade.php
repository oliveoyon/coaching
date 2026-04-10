<div class="admin-card p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="student_id" class="form-label fw-semibold">Student</label>
            <select id="student_id" name="student_id" class="form-select rounded-4" required>
                <option value="">Select student</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id', $override->student_id) === (string) $student->id)>{{ $student->name }} ({{ $student->student_code }})</option>
                @endforeach
            </select>
            @error('student_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="fee_structure_id" class="form-label fw-semibold">Fee Structure</label>
            <select id="fee_structure_id" name="fee_structure_id" class="form-select rounded-4" required>
                <option value="">Select structure</option>
                @foreach ($feeStructures as $feeStructure)
                    <option value="{{ $feeStructure->id }}" @selected((string) old('fee_structure_id', $override->fee_structure_id) === (string) $feeStructure->id)>{{ $feeStructure->title }} ({{ $feeStructure->feeHead?->name }})</option>
                @endforeach
            </select>
            @error('fee_structure_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="amount" class="form-label fw-semibold">Override Amount</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0" class="form-control rounded-4" value="{{ old('amount', $override->amount) }}" required>
            @error('amount') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="starts_on" class="form-label fw-semibold">Starts On</label>
            <input id="starts_on" name="starts_on" type="date" class="form-control rounded-4" value="{{ old('starts_on', optional($override->starts_on)->format('Y-m-d')) }}">
            @error('starts_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="ends_on" class="form-label fw-semibold">Ends On</label>
            <input id="ends_on" name="ends_on" type="date" class="form-control rounded-4" value="{{ old('ends_on', optional($override->ends_on)->format('Y-m-d')) }}">
            @error('ends_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
            <label for="reason" class="form-label fw-semibold">Reason</label>
            <textarea id="reason" name="reason" rows="4" class="form-control rounded-4">{{ old('reason', $override->reason) }}</textarea>
            @error('reason') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $override->is_active))>
                <label class="form-check-label fw-semibold" for="is_active">Active override</label>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('student-fee-overrides.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
