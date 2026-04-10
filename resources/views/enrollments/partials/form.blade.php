<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="admin-card p-4">
            <div class="page-section-title text-info-emphasis">Enrollment</div>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label for="student_id" class="form-label fw-semibold">Student</label>
                    <select id="student_id" name="student_id" class="form-select rounded-4" required>
                        <option value="">Select student</option>
                        @foreach ($students as $studentOption)
                            <option value="{{ $studentOption->id }}" @selected((string) old('student_id', $enrollment->student_id) === (string) $studentOption->id)>
                                {{ $studentOption->name }} ({{ $studentOption->student_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="batch_id" class="form-label fw-semibold">Batch</label>
                    <select id="batch_id" name="batch_id" class="form-select rounded-4" required>
                        <option value="">Select batch</option>
                        @foreach ($batches as $batchOption)
                            <option value="{{ $batchOption->id }}" @selected((string) old('batch_id', $enrollment->batch_id) === (string) $batchOption->id)>
                                {{ $batchOption->name }} ({{ $batchOption->code }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Teacher ownership is inferred from the selected batch owner, not stored again on the enrollment record.</div>
                    @error('batch_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="enrolled_at" class="form-label fw-semibold">Enrollment Date</label>
                    <input id="enrolled_at" name="enrolled_at" type="date" class="form-control rounded-4" value="{{ old('enrolled_at', filled($enrollment->enrolled_at) ? \Illuminate\Support\Carbon::parse((string) $enrollment->enrolled_at)->format('Y-m-d') : '') }}">
                    @error('enrolled_at') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select rounded-4">
                        <option value="{{ \App\Models\StudentEnrollment::STATUS_ACTIVE }}" @selected(old('status', $enrollment->status) === \App\Models\StudentEnrollment::STATUS_ACTIVE)>Active</option>
                        <option value="{{ \App\Models\StudentEnrollment::STATUS_INACTIVE }}" @selected(old('status', $enrollment->status) === \App\Models\StudentEnrollment::STATUS_INACTIVE)>Inactive</option>
                    </select>
                    @error('status') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="form-control rounded-4">{{ old('notes', $enrollment->notes) }}</textarea>
                    <div class="form-text">Use this for enrollment-specific remarks only. Billing and fee logic will remain separate.</div>
                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="admin-card p-4">
            <div class="page-section-title text-warning-emphasis">Scope Note</div>
            <p class="text-secondary mb-3">
                {{ $isTeacherScoped ? 'As a teacher, you can only work with your own students and your own batches.' : 'As admin, you can enroll any tenant student into any tenant batch.' }}
            </p>
            <div class="small text-secondary">
                One student can have multiple enrollment rows across different batches later. This module only prevents duplicate enrollment in the same batch.
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('enrollments.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
