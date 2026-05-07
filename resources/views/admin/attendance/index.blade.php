@extends('layouts.admin')

@section('title', 'Attendance')
@section('page-title', 'Attendance')
@section('page-subtitle', 'Open attendance and mark students quickly.')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div class="card page-card flex-grow-1">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="batch_id" class="form-label">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-select">
                            <option value="">{{ auth()->user()->hasRole('Teacher') ? 'My batches' : 'All visible batches' }}</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}" @selected($batchId === $batch->id)>{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="attendance_date" class="form-label">Date</label>
                        <input type="date" name="attendance_date" id="attendance_date" value="{{ $attendanceDate }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="in_progress" @selected($status === 'in_progress')>In Progress</option>
                            <option value="completed" @selected($status === 'completed')>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary">Open Attendance</a>
    </div>

    <div class="row g-4">
        @forelse ($sessions as $session)
            <div class="col-xl-6">
                <div class="card page-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h2 class="h5 mb-1">{{ $session->batch?->name }}</h2>
                                <div class="text-muted small">
                                    {{ $session->batch?->academicClass?->name }}
                                    @if ($session->batch?->subject)
                                        | {{ $session->batch?->subject?->name }}
                                    @endif
                                </div>
                            </div>
                            <span class="badge rounded-pill {{ $session->status === 'completed' ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ str_replace('_', ' ', ucfirst($session->status)) }}
                            </span>
                        </div>

                        <div class="row g-3 small mb-3">
                            <div class="col-sm-6">
                                <div class="text-muted">Attendance Date</div>
                                <div class="fw-semibold">{{ $session->attendance_date?->format('d M Y') }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted">Session Mode</div>
                                <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $session->mode) }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted">Students Loaded</div>
                                <div class="fw-semibold">{{ $session->records_count }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted">Opened By</div>
                                <div class="fw-semibold">{{ $session->creator?->name ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="small text-muted mb-3">
                            Teachers: {{ $session->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: 'Not assigned' }}
                        </div>

                        <a href="{{ route('admin.attendance.show', $session) }}" class="btn btn-outline-primary">Open Workspace</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card page-card">
                    <div class="card-body py-5 text-center text-muted">
                        No attendance session found yet for the selected filters.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
@endsection
