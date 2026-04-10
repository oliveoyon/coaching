<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="admin-card p-4">
            <div class="page-section-title text-info-emphasis">Profile</div>
            <div class="row g-3 mt-1">
                <div class="col-md-5">
                    <label for="student_code" class="form-label fw-semibold">Student ID</label>
                    <input id="student_code" name="student_code" type="text" class="form-control rounded-4" value="{{ old('student_code', $student->student_code) }}" required>
                    @error('student_code') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-7">
                    <label for="name" class="form-label fw-semibold">Student Name</label>
                    <input id="name" name="name" type="text" class="form-control rounded-4" value="{{ old('name', $student->name) }}" required>
                    @error('name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Phone</label>
                    <input id="phone" name="phone" type="text" class="form-control rounded-4" value="{{ old('phone', $student->phone) }}">
                    @error('phone') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input id="email" name="email" type="email" class="form-control rounded-4" value="{{ old('email', $student->email) }}">
                    @error('email') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="institution_name" class="form-label fw-semibold">School or College</label>
                    <input id="institution_name" name="institution_name" type="text" class="form-control rounded-4" value="{{ old('institution_name', $student->institution_name) }}">
                    @error('institution_name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="institution_class" class="form-label fw-semibold">Class / Group</label>
                    <input id="institution_class" name="institution_class" type="text" class="form-control rounded-4" value="{{ old('institution_class', $student->institution_class) }}" placeholder="Class 10 / HSC Science">
                    @error('institution_class') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <textarea id="address" name="address" rows="3" class="form-control rounded-4">{{ old('address', $student->address) }}</textarea>
                    @error('address') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="form-control rounded-4">{{ old('notes', $student->notes) }}</textarea>
                    <div class="form-text">Keep profile notes only here. Enrollment and billing details will stay in separate modules.</div>
                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="admin-card p-4 mb-4">
            <div class="page-section-title text-warning-emphasis">Academic Ownership</div>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label for="user_id" class="form-label fw-semibold">Linked Student User</label>
                    <select id="user_id" name="user_id" class="form-select rounded-4">
                        <option value="">No linked user</option>
                        @foreach ($availableUsers as $availableUser)
                            <option value="{{ $availableUser->id }}" @selected(old('user_id', $student->user_id) == $availableUser->id)>
                                {{ $availableUser->name }} ({{ $availableUser->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="owner_teacher_id" class="form-label fw-semibold">Teacher Owner</label>
                    @if ($isTeacherManaged)
                        <input type="hidden" name="owner_teacher_id" value="{{ old('owner_teacher_id', $student->owner_teacher_id) }}">
                        <div class="form-control rounded-4 bg-body-tertiary">{{ $teacherOwners->first()?->name ?? 'No teacher profile available' }}</div>
                    @else
                        <select id="owner_teacher_id" name="owner_teacher_id" class="form-select rounded-4">
                            @foreach ($teacherOwners as $teacherOwner)
                                <option value="{{ $teacherOwner->id }}" @selected(old('owner_teacher_id', $student->owner_teacher_id) == $teacherOwner->id)>{{ $teacherOwner->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <div class="form-text">This is the primary academic owner for scope and future batch ownership logic.</div>
                    @error('owner_teacher_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="admission_date" class="form-label fw-semibold">Admission Date</label>
                    <input id="admission_date" name="admission_date" type="date" class="form-control rounded-4" value="{{ old('admission_date', filled($student->admission_date) ? \Illuminate\Support\Carbon::parse((string) $student->admission_date)->format('Y-m-d') : '') }}">
                    @error('admission_date') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select rounded-4">
                        <option value="{{ \App\Models\Student::STATUS_ACTIVE }}" @selected(old('status', $student->status) === \App\Models\Student::STATUS_ACTIVE)>Active</option>
                        <option value="{{ \App\Models\Student::STATUS_INACTIVE }}" @selected(old('status', $student->status) === \App\Models\Student::STATUS_INACTIVE)>Inactive</option>
                        <option value="{{ \App\Models\Student::STATUS_DROPOUT }}" @selected(old('status', $student->status) === \App\Models\Student::STATUS_DROPOUT)>Dropout</option>
                        <option value="{{ \App\Models\Student::STATUS_COMPLETED }}" @selected(old('status', $student->status) === \App\Models\Student::STATUS_COMPLETED)>Completed</option>
                    </select>
                    @error('status') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="admin-card p-4">
            <div class="page-section-title text-success-emphasis">Primary Guardian</div>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label for="guardian_user_id" class="form-label fw-semibold">Linked Guardian User</label>
                    <select id="guardian_user_id" name="guardian_user_id" class="form-select rounded-4">
                        <option value="">No linked guardian user</option>
                        @foreach ($availableGuardianUsers as $availableGuardianUser)
                            <option value="{{ $availableGuardianUser->id }}" @selected(old('guardian_user_id', $guardian?->user_id) == $availableGuardianUser->id)>
                                {{ $availableGuardianUser->name }} ({{ $availableGuardianUser->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('guardian_user_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_name" class="form-label fw-semibold">Guardian Name</label>
                    <input id="guardian_name" name="guardian_name" type="text" class="form-control rounded-4" value="{{ old('guardian_name', $guardian?->name) }}">
                    @error('guardian_name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_relation_type" class="form-label fw-semibold">Relation</label>
                    <select id="guardian_relation_type" name="guardian_relation_type" class="form-select rounded-4">
                        <option value="">Select relation</option>
                        @foreach ($relationTypes as $relationType)
                            @php($selectedRelation = old('guardian_relation_type', $guardian?->pivot?->relation_type))
                            <option value="{{ $relationType }}" @selected($selectedRelation === $relationType)>{{ ucfirst($relationType) }}</option>
                        @endforeach
                    </select>
                    @error('guardian_relation_type') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_phone" class="form-label fw-semibold">Guardian Phone</label>
                    <input id="guardian_phone" name="guardian_phone" type="text" class="form-control rounded-4" value="{{ old('guardian_phone', $guardian?->phone) }}">
                    @error('guardian_phone') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_email" class="form-label fw-semibold">Guardian Email</label>
                    <input id="guardian_email" name="guardian_email" type="email" class="form-control rounded-4" value="{{ old('guardian_email', $guardian?->email) }}">
                    @error('guardian_email') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_occupation" class="form-label fw-semibold">Occupation</label>
                    <input id="guardian_occupation" name="guardian_occupation" type="text" class="form-control rounded-4" value="{{ old('guardian_occupation', $guardian?->occupation) }}">
                    @error('guardian_occupation') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_notes" class="form-label fw-semibold">Relation Note</label>
                    <input id="guardian_notes" name="guardian_notes" type="text" class="form-control rounded-4" value="{{ old('guardian_notes', $guardian?->pivot?->notes) }}" placeholder="Primary contact">
                    @error('guardian_notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="guardian_address" class="form-label fw-semibold">Guardian Address</label>
                    <textarea id="guardian_address" name="guardian_address" rows="3" class="form-control rounded-4">{{ old('guardian_address', $guardian?->address) }}</textarea>
                    @error('guardian_address') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="form-text">This form manages one primary guardian now, while the schema already supports multiple guardians later.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('students.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
