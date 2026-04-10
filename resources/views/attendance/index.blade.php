@extends('layouts.app')

@section('title', 'Attendance')

@section('page_header')
    <div>
        <div class="page-section-title">Academic Operations</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Attendance</h1>
        <p class="text-secondary mb-0">Review batch-wise attendance sessions and open a date for marking or correction.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-4 mb-4">
            <form action="{{ route('attendance.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="batch_id" class="form-label fw-semibold">Batch</label>
                    <select id="batch_id" name="batch_id" class="form-select rounded-4">
                        <option value="">All visible batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) request('batch_id') === (string) $batch->id)>{{ $batch->name }} ({{ $batch->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="attendance_date" class="form-label fw-semibold">Attendance Date</label>
                    <input id="attendance_date" type="date" name="attendance_date" class="form-control rounded-4" value="{{ request('attendance_date') }}">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-outline-dark rounded-4">Filter Sessions</button>
                </div>
            </form>
        </div>

        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="page-section-title text-primary-emphasis">Recent Sessions</div>
                <a href="{{ route('attendance.create') }}" class="btn btn-dark rounded-pill px-4">Take Attendance</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch</th>
                            <th>Owner Teacher</th>
                            <th>Taken By</th>
                            <th>Entries</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td>{{ $session->attendance_date?->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $session->batch?->name }}</div>
                                    <div class="small text-secondary">{{ $session->batch?->code }}</div>
                                </td>
                                <td>{{ $session->ownerTeacher?->name ?? 'N/A' }}</td>
                                <td>{{ $session->takenBy?->name ?? 'N/A' }}</td>
                                <td>{{ $session->records->count() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('attendance.create', ['batch_id' => $session->batch_id, 'attendance_date' => $session->attendance_date?->toDateString()]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        {{ auth()->user()->isAdmin() ? 'Review / Override' : 'Review / Update' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">No attendance sessions found for the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
@endsection
