@php
    $scheduleDays = old('schedule_days', $batch->relationLoaded('schedules') ? $batch->schedules->pluck('day_of_week')->all() : []);
    $scheduleStarts = old('schedule_start_times', $batch->relationLoaded('schedules') ? $batch->schedules->pluck('start_time')->map(fn ($time) => substr($time, 0, 5))->all() : []);
    $scheduleEnds = old('schedule_end_times', $batch->relationLoaded('schedules') ? $batch->schedules->pluck('end_time')->map(fn ($time) => substr($time, 0, 5))->all() : []);
    $scheduleRooms = old('schedule_rooms', $batch->relationLoaded('schedules') ? $batch->schedules->pluck('room_name')->all() : []);
    $rowCount = max(count($scheduleDays), 1);
@endphp

<div class="row g-4">
    <div class="col-xl-7">
        <div class="admin-card p-4">
            <h5 class="fw-bold mb-3">Batch Details</h5>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Batch Name</label>
                    <input type="text" name="name" class="form-control rounded-4" value="{{ old('name', $batch->name) }}" required>
                    @error('name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Code</label>
                    <input type="text" name="code" class="form-control rounded-4" value="{{ old('code', $batch->code) }}" required>
                    @error('code') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Program / Class</label>
                    <select name="program_id" class="form-select rounded-4">
                        <option value="">Select program</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id', $batch->program_id) == $program->id)>
                                {{ $program->name }}{{ $program->code ? ' ('.$program->code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subject</label>
                    <select name="subject_id" class="form-select rounded-4">
                        <option value="">Select subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id', $batch->subject_id) == $subject->id)>
                                {{ $subject->name }}{{ $subject->code ? ' ('.$subject->code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teacher Owner</label>
                    @if ($isTeacherScoped)
                        <input type="hidden" name="owner_teacher_id" value="{{ auth()->user()->teacher?->id }}">
                        <div class="form-control rounded-4 bg-body-tertiary">{{ auth()->user()->teacher?->name }}</div>
                    @else
                        <select name="owner_teacher_id" class="form-select rounded-4" required>
                            <option value="">Select teacher owner</option>
                            @foreach ($ownerTeachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('owner_teacher_id', $batch->owner_teacher_id) == $teacher->id)>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('owner_teacher_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select rounded-4">
                        <option value="{{ \App\Models\Batch::STATUS_ACTIVE }}" @selected(old('status', $batch->status) === \App\Models\Batch::STATUS_ACTIVE)>Active</option>
                        <option value="{{ \App\Models\Batch::STATUS_INACTIVE }}" @selected(old('status', $batch->status) === \App\Models\Batch::STATUS_INACTIVE)>Inactive</option>
                        <option value="{{ \App\Models\Batch::STATUS_COMPLETED }}" @selected(old('status', $batch->status) === \App\Models\Batch::STATUS_COMPLETED)>Completed</option>
                    </select>
                    @error('status') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Capacity</label>
                    <input type="number" min="1" name="capacity" class="form-control rounded-4" value="{{ old('capacity', $batch->capacity) }}">
                    @error('capacity') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Room</label>
                    <input type="text" name="room_name" class="form-control rounded-4" value="{{ old('room_name', $batch->room_name) }}">
                    @error('room_name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Starts On</label>
                    <input type="date" name="starts_on" class="form-control rounded-4" value="{{ old('starts_on', optional($batch->starts_on)->format('Y-m-d')) }}">
                    @error('starts_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ends On</label>
                    <input type="date" name="ends_on" class="form-control rounded-4" value="{{ old('ends_on', optional($batch->ends_on)->format('Y-m-d')) }}">
                    @error('ends_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" rows="4" class="form-control rounded-4">{{ old('notes', $batch->notes) }}</textarea>
                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Routine Ready Schedule</h5>
                    <div class="small text-secondary">Add schedule rows now so future routine, WhatsApp batch groups, and exam planning can plug in cleanly.</div>
                </div>
                <button type="button" class="btn btn-outline-primary rounded-4 btn-sm" id="addScheduleRow">
                    <i class="bi bi-plus-circle me-1"></i>Add Row
                </button>
            </div>

            <div id="scheduleRows" class="d-grid gap-3">
                @for ($i = 0; $i < $rowCount; $i++)
                    <div class="border rounded-4 p-3 schedule-row">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Day</label>
                                <select name="schedule_days[]" class="form-select rounded-4">
                                    <option value="">Select day</option>
                                    @foreach (\App\Models\BatchSchedule::DAYS as $day)
                                        <option value="{{ $day }}" @selected(($scheduleDays[$i] ?? null) === $day)>{{ ucfirst($day) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Start</label>
                                <input type="time" name="schedule_start_times[]" class="form-control rounded-4" value="{{ $scheduleStarts[$i] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">End</label>
                                <input type="time" name="schedule_end_times[]" class="form-control rounded-4" value="{{ $scheduleEnds[$i] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Room</label>
                                <div class="d-flex gap-2">
                                    <input type="text" name="schedule_rooms[]" class="form-control rounded-4" value="{{ $scheduleRooms[$i] ?? '' }}" placeholder="Room or online meeting note">
                                    <button type="button" class="btn btn-outline-danger rounded-4 remove-schedule-row">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            @error('schedule_days') <div class="text-danger small mt-3">{{ $message }}</div> @enderror
            @error('schedule_end_times') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('batches.index') }}" class="btn btn-light rounded-4 px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-primary rounded-4 px-4 fw-semibold">{{ $submitLabel }}</button>
</div>

<template id="scheduleRowTemplate">
    <div class="border rounded-4 p-3 schedule-row">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Day</label>
                <select name="schedule_days[]" class="form-select rounded-4">
                    <option value="">Select day</option>
                    @foreach (\App\Models\BatchSchedule::DAYS as $day)
                        <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Start</label>
                <input type="time" name="schedule_start_times[]" class="form-control rounded-4">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">End</label>
                <input type="time" name="schedule_end_times[]" class="form-control rounded-4">
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Room</label>
                <div class="d-flex gap-2">
                    <input type="text" name="schedule_rooms[]" class="form-control rounded-4" placeholder="Room or online meeting note">
                    <button type="button" class="btn btn-outline-danger rounded-4 remove-schedule-row">Remove</button>
                </div>
            </div>
        </div>
    </div>
</template>
