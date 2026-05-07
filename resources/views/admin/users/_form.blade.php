@csrf

<div class="border rounded-4 p-4 mb-4">
    <h2 class="h5 mb-3">User Details</h2>

    <div class="row g-4">
        <div class="col-md-6">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" value="{{ old('username', $user->username ?? '') }}" class="form-control @error('username') is-invalid @enderror" required>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Select role</option>
                @foreach ($roles as $roleValue => $roleLabel)
                    <option value="{{ $roleValue }}" @selected(old('role', isset($user) ? $user->roles->first()?->name : null) === $roleValue)>{{ $roleLabel }}</option>
                @endforeach
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $user->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="border rounded-4 p-4 mb-4">
    <h2 class="h5 mb-3">Password</h2>

    <div class="row g-4">
        <div class="col-md-6">
            <label for="password" class="form-label">{{ isset($user) ? 'New Password' : 'Password' }}</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">{{ isset($user) ? 'Confirm New Password' : 'Confirm Password' }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
