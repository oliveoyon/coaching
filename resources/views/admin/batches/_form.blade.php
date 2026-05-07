@csrf

@php
    $batch = $batch ?? null;
    $selectedTeacherIds = collect(old('teacher_ids', isset($batch) ? $batch->teachers->pluck('id')->all() : []))->map(fn ($id) => (string) $id);
    $weekdayOptions = [
        'sat' => 'Saturday',
        'sun' => 'Sunday',
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
    ];
    $scheduleSlots = old('schedule_slots', isset($batch) ? $batch->schedule_entries : [['day' => '', 'start_time' => '', 'end_time' => '']]);

    if ($scheduleSlots === [] || $scheduleSlots === null) {
        $scheduleSlots = [['day' => '', 'start_time' => '', 'end_time' => '']];
    }
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="fw-semibold mb-3">Basic Info</div>
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
                        <option value="">Select</option>
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
                        <option value="">General</option>
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
                    <label for="distribution_type" class="form-label">Distribution</label>
                    <select name="distribution_type" id="distribution_type" class="form-select @error('distribution_type') is-invalid @enderror" required>
                        <option value="single" @selected(old('distribution_type', $batch->distribution_type ?? 'single') === 'single')>Single</option>
                        <option value="equal" @selected(old('distribution_type', $batch->distribution_type ?? 'single') === 'equal')>Equal</option>
                    </select>
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
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="fw-semibold mb-3">Schedule</div>
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <div class="small text-muted">Add one or more class times</div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-schedule-slot">
                    <i class="bi bi-plus-circle me-1"></i> Add Time
                </button>
            </div>

            <div id="schedule-slots" class="d-flex flex-column gap-3">
                @foreach ($scheduleSlots as $index => $slot)
                    <div class="border rounded-4 p-3 schedule-slot">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Day</label>
                                <select name="schedule_slots[{{ $index }}][day]" class="form-select @error("schedule_slots.$index.day") is-invalid @enderror">
                                    <option value="">Select</option>
                                    @foreach ($weekdayOptions as $dayValue => $dayLabel)
                                        <option value="{{ $dayValue }}" @selected(($slot['day'] ?? '') === $dayValue)>{{ $dayLabel }}</option>
                                    @endforeach
                                </select>
                                @error("schedule_slots.$index.day")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Start</label>
                                <input type="time" name="schedule_slots[{{ $index }}][start_time]" value="{{ $slot['start_time'] ?? '' }}" class="form-control @error("schedule_slots.$index.start_time") is-invalid @enderror">
                                @error("schedule_slots.$index.start_time")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">End</label>
                                <input type="time" name="schedule_slots[{{ $index }}][end_time]" value="{{ $slot['end_time'] ?? '' }}" class="form-control @error("schedule_slots.$index.end_time") is-invalid @enderror">
                                @error("schedule_slots.$index.end_time")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-schedule-slot" @disabled(count($scheduleSlots) === 1 && $index === 0)>Remove</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @error('schedule_slots')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror
            @error('schedule_slots.*.day')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror
            @error('schedule_slots.*.start_time')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror
            @error('schedule_slots.*.end_time')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <template id="schedule-slot-template">
        <div class="border rounded-4 p-3 schedule-slot">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Day</label>
                    <select name="__NAME__[day]" class="form-select">
                        <option value="">Select</option>
                        @foreach ($weekdayOptions as $dayValue => $dayLabel)
                            <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start</label>
                    <input type="time" name="__NAME__[start_time]" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End</label>
                    <input type="time" name="__NAME__[end_time]" class="form-control">
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 remove-schedule-slot">Remove</button>
                </div>
            </div>
        </div>
    </template>

    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="fw-semibold mb-3">Teachers</div>
            @if ($teachers->isEmpty())
                <div class="text-muted">No active teacher found.</div>
            @else
                <div class="row g-3">
                    @foreach ($teachers as $teacher)
                        <div class="col-md-6 col-xl-4">
                            <input
                                class="btn-check"
                                type="checkbox"
                                name="teacher_ids[]"
                                value="{{ $teacher->id }}"
                                id="teacher_{{ $teacher->id }}"
                                @checked($selectedTeacherIds->contains((string) $teacher->id))
                            >
                            <label class="btn btn-outline-primary text-start w-100 h-100 rounded-4 p-3" for="teacher_{{ $teacher->id }}">
                                <div class="fw-semibold">{{ $teacher->user->name }}</div>
                                <div class="small text-muted">{{ $teacher->user->email }}</div>
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
            @error('teacher_ids')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror
            @error('teacher_ids.*')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

@push('scripts')
    <script>
        (() => {
            const slotContainer = document.getElementById('schedule-slots');
            const addButton = document.getElementById('add-schedule-slot');
            const template = document.getElementById('schedule-slot-template');

            if (!slotContainer || !addButton || !template) {
                return;
            }

            const updateRemoveButtons = () => {
                const rows = slotContainer.querySelectorAll('.schedule-slot');

                rows.forEach((row, index) => {
                    const button = row.querySelector('.remove-schedule-slot');

                    if (!button) {
                        return;
                    }

                    button.disabled = rows.length === 1 && index === 0;
                });
            };

            const bindRemoveButtons = () => {
                slotContainer.querySelectorAll('.remove-schedule-slot').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }

                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const row = button.closest('.schedule-slot');

                        if (!row) {
                            return;
                        }

                        row.remove();
                        updateRemoveButtons();
                    });
                });
            };

            addButton.addEventListener('click', () => {
                const nextIndex = slotContainer.querySelectorAll('.schedule-slot').length;
                const html = template.innerHTML.replaceAll('__NAME__', `schedule_slots[${nextIndex}]`);
                slotContainer.insertAdjacentHTML('beforeend', html);
                bindRemoveButtons();
                updateRemoveButtons();
            });

            bindRemoveButtons();
            updateRemoveButtons();
        })();
    </script>
@endpush
