<div class="row g-4">
    <div class="col-xl-8">
        <div class="admin-card p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="batch_id" class="form-label fw-semibold">Batch</label>
                    <select id="batch_id" name="batch_id" class="form-select rounded-4" required>
                        <option value="">Select batch</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) old('batch_id', $schedule->batch_id) === (string) $batch->id)>{{ $batch->name }} ({{ $batch->code }})</option>
                        @endforeach
                    </select>
                    @error('batch_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="subject_id" class="form-label fw-semibold">Subject</label>
                    <select id="subject_id" name="subject_id" class="form-select rounded-4">
                        <option value="">Use batch subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $schedule->subject_id) === (string) $subject->id)>{{ $subject->name }}{{ $subject->code ? ' ('.$subject->code.')' : '' }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">For now this should generally match the batch subject.</div>
                    @error('subject_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="teacher_id" class="form-label fw-semibold">Teacher</label>
                    @if ($isTeacherScoped)
                        <input type="hidden" name="teacher_id" value="{{ auth()->user()->teacher?->id }}">
                        <div class="form-control rounded-4 bg-body-tertiary">{{ auth()->user()->teacher?->name }}</div>
                    @else
                        <select id="teacher_id" name="teacher_id" class="form-select rounded-4" required>
                            <option value="">Select teacher</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $schedule->teacher_id ?: $selectedBatch?->owner_teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('teacher_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="day_of_week" class="form-label fw-semibold">Day</label>
                    <select id="day_of_week" name="day_of_week" class="form-select rounded-4" required>
                        @foreach ($days as $day)
                            <option value="{{ $day }}" @selected(old('day_of_week', $schedule->day_of_week) === $day)>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                    @error('day_of_week') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="start_time" class="form-label fw-semibold">Start Time</label>
                    <input id="start_time" type="time" name="start_time" class="form-control rounded-4" value="{{ old('start_time', $schedule->start_time ? substr((string) $schedule->start_time, 0, 5) : '') }}" required>
                    @error('start_time') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="end_time" class="form-label fw-semibold">End Time</label>
                    <input id="end_time" type="time" name="end_time" class="form-control rounded-4" value="{{ old('end_time', $schedule->end_time ? substr((string) $schedule->end_time, 0, 5) : '') }}" required>
                    @error('end_time') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="sort_order" class="form-label fw-semibold">Sort Order</label>
                    <input id="sort_order" type="number" min="1" name="sort_order" class="form-control rounded-4" value="{{ old('sort_order', $schedule->sort_order ?: 1) }}">
                    @error('sort_order') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="room_name" class="form-label fw-semibold">Room</label>
                    <input id="room_name" type="text" name="room_name" class="form-control rounded-4" value="{{ old('room_name', $schedule->room_name) }}" placeholder="Optional room or online link note">
                    @error('room_name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="session_type" class="form-label fw-semibold">Session Type</label>
                    <select id="session_type" name="session_type" class="form-select rounded-4">
                        @foreach ($sessionTypes as $type)
                            <option value="{{ $type }}" @selected(old('session_type', $schedule->session_type ?: \App\Models\BatchSchedule::SESSION_TYPE_REGULAR) === $type)>{{ str($type)->title() }}</option>
                        @endforeach
                    </select>
                    @error('session_type') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="border rounded-4 p-3">
                        <input type="hidden" name="is_extra" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_extra" name="is_extra" value="1" @checked(old('is_extra', $schedule->is_extra))>
                            <label class="form-check-label fw-semibold" for="is_extra">Mark as extra class</label>
                        </div>
                        <div class="small text-secondary mt-2">This keeps the routine future-ready for special makeup or extra classes.</div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="form-control rounded-4">{{ old('notes', $schedule->notes) }}</textarea>
                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card p-4">
            <div class="page-section-title text-primary-emphasis">Conflict Checks</div>
            <ul class="small text-secondary ps-3 mb-0">
                <li>Same batch cannot have overlapping time slots on the same day.</li>
                <li>Same teacher cannot be double-booked on the same day and time.</li>
                <li>Same room cannot be double-booked for overlapping classes.</li>
            </ul>
        </div>

        <div class="admin-card p-4 mt-4">
            <div class="page-section-title text-success-emphasis">Future Fit</div>
            <div class="small text-secondary">
                This routine structure connects naturally with batch attendance, teacher schedule views, and later extra class handling or reminders.
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('schedules.index') }}" class="btn btn-light rounded-4 px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-4 px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
