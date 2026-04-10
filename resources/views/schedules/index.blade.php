@extends('layouts.app')

@section('title', 'Routine & Schedule')

@section('page_header')
    <div>
        <div class="page-section-title">Academic Operations</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Routine & Class Schedule</h1>
        <p class="text-secondary mb-0">Manage recurring batch routines that teachers can follow and attendance can reference naturally.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-4 mb-4">
            <form action="{{ route('schedules.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="batch_id" class="form-label fw-semibold">Batch</label>
                    <select id="batch_id" name="batch_id" class="form-select rounded-4">
                        <option value="">All visible batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) request('batch_id') === (string) $batch->id)>{{ $batch->name }} ({{ $batch->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="teacher_id" class="form-label fw-semibold">Teacher</label>
                    <select id="teacher_id" name="teacher_id" class="form-select rounded-4">
                        <option value="">All visible teachers</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="day_of_week" class="form-label fw-semibold">Day</label>
                    <select id="day_of_week" name="day_of_week" class="form-select rounded-4">
                        <option value="">All days</option>
                        @foreach ($days as $day)
                            <option value="{{ $day }}" @selected(request('day_of_week') === $day)>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-dark rounded-4">Filter</button>
                </div>
            </form>
        </div>

        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="page-section-title text-primary-emphasis">Routine List</div>
                <a href="{{ route('schedules.create', ['batch_id' => request('batch_id')]) }}" class="btn btn-dark rounded-pill px-4">Add Schedule</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Batch</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td>{{ ucfirst($schedule->day_of_week) }}</td>
                                <td>{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $schedule->batch?->name }}</div>
                                    <div class="small text-secondary">{{ $schedule->batch?->code }}</div>
                                </td>
                                <td>{{ $schedule->subject?->name ?? $schedule->batch?->subject?->name ?? 'N/A' }}</td>
                                <td>{{ $schedule->teacher?->name ?? 'N/A' }}</td>
                                <td>{{ $schedule->room_name ?: 'N/A' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $schedule->is_extra ? 'text-bg-warning' : 'text-bg-primary' }}">
                                        {{ $schedule->is_extra ? 'Extra' : 'Regular' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Edit</a>
                                        <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" onsubmit="return confirm('Delete this schedule row?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-secondary">No schedule rows found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
@endsection
