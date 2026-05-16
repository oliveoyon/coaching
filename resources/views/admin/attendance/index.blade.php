@extends('layouts.admin')

@section('title', 'Attendance')
@section('page-title', 'Attendance')
@section('page-subtitle', 'Open attendance and mark students quickly.')

@section('content')
    <style>
        .attendance-session-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .attendance-session-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.06);
            color: #475569;
            font-size: 0.78rem;
        }

        .attendance-session-pill strong {
            color: #0f172a;
            font-weight: 600;
        }
    </style>

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

    @if ($isDefaultBoard)
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Today</h2>
                <div class="small text-muted">{{ $todayDate->format('d M Y') }} | Open sessions only</div>
            </div>
            @if ($sessions->total() > 0)
                <span class="small text-muted">{{ $sessions->total() }} session{{ $sessions->total() === 1 ? '' : 's' }}</span>
            @endif
        </div>
    @endif

    <div class="row g-4">
        @forelse ($sessions as $session)
            @php
                $sessionDay = strtolower($session->attendance_date?->format('D') ?? '');
                $sessionSlot = collect($session->batch?->schedule_entries ?? [])
                    ->filter(fn ($entry) => strtolower((string) ($entry['day'] ?? '')) === $sessionDay)
                    ->sortBy('start_time')
                    ->first();
                $timeLabel = $sessionSlot
                    ? \Carbon\Carbon::createFromFormat('H:i', $sessionSlot['start_time'])->format('g:i A').' - '.\Carbon\Carbon::createFromFormat('H:i', $sessionSlot['end_time'])->format('g:i A')
                    : 'Time not set';
            @endphp
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

                        <div class="attendance-session-meta mb-3">
                            <span class="attendance-session-pill"><strong>{{ $session->attendance_date?->format('d M Y') }}</strong></span>
                            <span class="attendance-session-pill"><strong>{{ $timeLabel }}</strong></span>
                            <span class="attendance-session-pill"><strong>{{ $session->records_count }}</strong> Students</span>
                            <span class="attendance-session-pill"><strong>{{ str_replace('_', ' ', ucfirst($session->mode)) }}</strong></span>
                        </div>

                        <div class="small text-muted mb-3">
                            Teachers: {{ $session->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: 'Not assigned' }}
                        </div>

                        <div class="small text-muted mb-3">
                            Opened by: {{ $session->creator?->name ?: '-' }}
                        </div>

                        <a href="{{ route('admin.attendance.show', $session) }}" class="btn btn-outline-primary">Open Workspace</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card page-card">
                    <div class="card-body py-5 text-center text-muted">
                        {{ $isDefaultBoard ? 'No open attendance session for today.' : 'No attendance session found yet for the selected filters.' }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
@endsection
