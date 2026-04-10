@php
    $specializations = old('subject_specializations', collect($teacher->subject_specializations)->join(', '));
    $isSelfManaged = $isSelfManaged ?? false;
@endphp

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="admin-card p-4">
            <div class="page-section-title text-info-emphasis">Profile</div>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label for="name" class="form-label fw-semibold">Teacher Name</label>
                    <input id="name" name="name" type="text" class="form-control rounded-4" value="{{ old('name', $teacher->name) }}" required>
                    @error('name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Phone</label>
                    <input id="phone" name="phone" type="text" class="form-control rounded-4" value="{{ old('phone', $teacher->phone) }}">
                    @error('phone') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input id="email" name="email" type="email" class="form-control rounded-4" value="{{ old('email', $teacher->email) }}">
                    @error('email') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="subject_specializations" class="form-label fw-semibold">Subject Specializations</label>
                    <textarea id="subject_specializations" name="subject_specializations" rows="3" class="form-control rounded-4">{{ $specializations }}</textarea>
                    <div class="form-text">Use comma-separated values like `Math, Physics, ICT`.</div>
                    @error('subject_specializations') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <textarea id="address" name="address" rows="3" class="form-control rounded-4">{{ old('address', $teacher->address) }}</textarea>
                    @error('address') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="bio" class="form-label fw-semibold">Short Bio</label>
                    <textarea id="bio" name="bio" rows="4" class="form-control rounded-4">{{ old('bio', $teacher->bio) }}</textarea>
                    @error('bio') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="admin-card p-4">
            <div class="page-section-title text-warning-emphasis">Access</div>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label for="user_id" class="form-label fw-semibold">Linked User Account</label>
                    @if ($isSelfManaged)
                        <input type="hidden" name="user_id" value="{{ $teacher->user_id }}">
                        <div class="form-control rounded-4 bg-body-tertiary">{{ $teacher->user?->email ?? 'No linked account available' }}</div>
                    @else
                        <select id="user_id" name="user_id" class="form-select rounded-4">
                            <option value="">No linked user</option>
                            @foreach ($availableUsers as $availableUser)
                                <option value="{{ $availableUser->id }}" @selected(old('user_id', $teacher->user_id) == $availableUser->id)>
                                    {{ $availableUser->name }} ({{ $availableUser->email }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('user_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    @if ($isSelfManaged)
                        <input type="hidden" name="status" value="{{ $teacher->status ?? \App\Models\Teacher::STATUS_ACTIVE }}">
                        <div class="form-control rounded-4 bg-body-tertiary">{{ ucfirst($teacher->status ?? \App\Models\Teacher::STATUS_ACTIVE) }}</div>
                    @else
                        <select id="status" name="status" class="form-select rounded-4">
                            <option value="{{ \App\Models\Teacher::STATUS_ACTIVE }}" @selected(old('status', $teacher->status) === \App\Models\Teacher::STATUS_ACTIVE)>Active</option>
                            <option value="{{ \App\Models\Teacher::STATUS_INACTIVE }}" @selected(old('status', $teacher->status) === \App\Models\Teacher::STATUS_INACTIVE)>Inactive</option>
                        </select>
                    @endif
                    @error('status') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="joined_at" class="form-label fw-semibold">Joined Date</label>
                    @if ($isSelfManaged)
                        <input type="hidden" name="joined_at" value="{{ optional($teacher->joined_at)->format('Y-m-d') }}">
                        <div class="form-control rounded-4 bg-body-tertiary">{{ optional($teacher->joined_at)->format('Y-m-d') ?: 'Not set' }}</div>
                    @else
                        <input id="joined_at" name="joined_at" type="date" class="form-control rounded-4" value="{{ old('joined_at', optional($teacher->joined_at)->format('Y-m-d')) }}">
                    @endif
                    @error('joined_at') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="border rounded-4 p-3">
                        <input type="hidden" name="can_own_batches" value="{{ (int) old('can_own_batches', $teacher->can_own_batches) }}">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_own_batches" value="1" id="can_own_batches" @checked(old('can_own_batches', $teacher->can_own_batches)) @disabled($isSelfManaged)>
                            <label class="form-check-label fw-semibold" for="can_own_batches">Can own batches</label>
                        </div>
                        <div class="small text-secondary mt-2">This teacher may become `owner_teacher_id` in future batches and student ownership logic.</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="border rounded-4 p-3">
                        <input type="hidden" name="can_collect_fees" value="{{ (int) old('can_collect_fees', $teacher->can_collect_fees) }}">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_collect_fees" value="1" id="can_collect_fees" @checked(old('can_collect_fees', $teacher->can_collect_fees)) @disabled($isSelfManaged)>
                            <label class="form-check-label fw-semibold" for="can_collect_fees">Can collect fees</label>
                        </div>
                        <div class="small text-secondary mt-2">The linked teacher account may later be used as actual payment collector in fee collection records.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('teachers.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
