@csrf

<div class="row g-4">
    @isset($student)
        <div class="col-md-4">
            <label class="form-label">Student Code</label>
            <input type="text" class="form-control" value="{{ $student->student_code }}" disabled>
            <div class="form-text">Generated automatically and cannot be edited.</div>
        </div>

        <div class="col-md-4">
            <label class="form-label d-block">Current Photo</label>
            @if ($student->photoUrl())
                <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="img-thumbnail" style="max-width: 140px;">
            @else
                <div class="text-muted small">No photo uploaded.</div>
            @endif
        </div>
    @endisset

    <div class="col-md-6">
        <label for="name" class="form-label">Student Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $student->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="class_id" class="form-label">Class</label>
        <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
            <option value="">Select class</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected((string) old('class_id', $student->class_id ?? '') === (string) $class->id)>
                    {{ $class->name }}
                    @if ($class->status !== 'active')
                        (Inactive)
                    @endif
                </option>
            @endforeach
        </select>
        @error('class_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $student->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $student->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="phone" class="form-label">Student WhatsApp / Mobile</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="Optional">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="guardian_phone" class="form-label">Guardian WhatsApp / Mobile</label>
        <input type="text" name="guardian_phone" id="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone ?? '') }}" class="form-control @error('guardian_phone') is-invalid @enderror" required>
        @error('guardian_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="school" class="form-label">School</label>
        <input type="text" name="school" id="school" value="{{ old('school', $student->school ?? '') }}" class="form-control @error('school') is-invalid @enderror">
        @error('school')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="address" class="form-label">Address</label>
        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $student->address ?? '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="photo" class="form-label">Student Photo</label>
        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png">
        <div class="form-text">Recommended for future face-based attendance support.</div>
        @error('photo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
