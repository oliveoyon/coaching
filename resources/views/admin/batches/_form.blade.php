@csrf

@php
    $selectedScheduleDays = collect(old('schedule_days', $batch->schedule_days ?? []));
    $weekdayOptions = [
        'sat' => 'Saturday',
        'sun' => 'Sunday',
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
    ];
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Batch Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $batch->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="class_id" class="form-label">Class</label>
        <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
            <option value="">Select class</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected((string) old('class_id', $batch->class_id ?? '') === (string) $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
        @error('class_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="subject_id" class="form-label">Subject</label>
        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
            <option value="">General Batch</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) old('subject_id', $batch->subject_id ?? '') === (string) $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
        @error('subject_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="monthly_fee" class="form-label">Monthly Fee</label>
        <input type="number" step="0.01" min="0" name="monthly_fee" id="monthly_fee" value="{{ old('monthly_fee', isset($batch) ? number_format((float) $batch->monthly_fee, 2, '.', '') : '') }}" class="form-control @error('monthly_fee') is-invalid @enderror" required>
        @error('monthly_fee')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="distribution_type" class="form-label">Distribution Type</label>
        <select name="distribution_type" id="distribution_type" class="form-select @error('distribution_type') is-invalid @enderror" required>
            <option value="single" @selected(old('distribution_type', $batch->distribution_type ?? 'single') === 'single')>Single</option>
            <option value="equal" @selected(old('distribution_type', $batch->distribution_type ?? 'single') === 'equal')>Equal</option>
        </select>
        <div class="form-text">Single allows exactly one teacher. Equal allows one or more teachers.</div>
        @error('distribution_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $batch->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $batch->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label d-block">Class Days</label>
        <div class="d-flex flex-wrap gap-3">
            @foreach ($weekdayOptions as $dayValue => $dayLabel)
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="schedule_days[]"
                        value="{{ $dayValue }}"
                        id="schedule_day_{{ $dayValue }}"
                        @checked($selectedScheduleDays->contains($dayValue))
                    >
                    <label class="form-check-label" for="schedule_day_{{ $dayValue }}">
                        {{ $dayLabel }}
                    </label>
                </div>
            @endforeach
        </div>
        <div class="form-text">Optional. Use this for general batch timing reference only.</div>
        @error('schedule_days')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('schedule_days.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="start_time" class="form-label">Start Time</label>
        <input type="time" name="start_time" id="start_time" value="{{ old('start_time', isset($batch) && $batch->start_time ? $batch->start_time->format('H:i') : '') }}" class="form-control @error('start_time') is-invalid @enderror">
        @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="end_time" class="form-label">End Time</label>
        <input type="time" name="end_time" id="end_time" value="{{ old('end_time', isset($batch) && $batch->end_time ? $batch->end_time->format('H:i') : '') }}" class="form-control @error('end_time') is-invalid @enderror">
        @error('end_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="teacher_ids" class="form-label">Teachers</label>
        <select name="teacher_ids[]" id="teacher_ids" class="form-select @error('teacher_ids') is-invalid @enderror" multiple size="6" required>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(collect(old('teacher_ids', isset($batch) ? $batch->teachers->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->contains((string) $teacher->id))>
                    {{ $teacher->user->name }} ({{ $teacher->user->email }})
                </option>
            @endforeach
        </select>
        <div class="form-text">
            @if ($teachers->isEmpty())
                No active teachers found. Create teacher records first from Teacher Management.
            @else
                Hold Ctrl or Command to select multiple teachers for equal distribution.
            @endif
        </div>
        @error('teacher_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('teacher_ids.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
