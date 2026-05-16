@csrf

@php
    $teacher = $teacher ?? null;
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="border rounded-4 p-4 setup-form-section">
            <div class="fw-semibold mb-3 setup-form-section-title">Teacher Info</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Teacher Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $teacher?->user?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $teacher?->user?->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $teacher?->user?->username ?? '') }}" class="form-control @error('username') is-invalid @enderror" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" @selected(old('status', $teacher?->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $teacher?->status ?? 'active') === 'inactive')>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4 setup-form-section">
            <div class="fw-semibold mb-3 setup-form-section-title">Login Access</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">{{ isset($teacher) ? 'New Password' : 'Password' }}</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" {{ isset($teacher) ? '' : 'required' }}>
                    @if (isset($teacher))
                        <div class="small text-muted mt-2">Leave blank to keep the current password.</div>
                    @endif
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">{{ isset($teacher) ? 'Confirm New Password' : 'Confirm Password' }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ isset($teacher) ? '' : 'required' }}>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
