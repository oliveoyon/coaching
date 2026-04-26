@extends('layouts.admin')

@section('title', 'Enrollment History')
@section('page-title', auth()->user()->can('manage enrollments') ? 'Enrollment Management' : 'My Student Enrollments')
@section('page-subtitle', 'Admission and enrollment stay separate so future billing can follow active batch participation only.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.enrollments.index') }}" class="row g-2 w-100">
                    <div class="col-12 col-lg-5">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student code, student name, guardian phone, or batch">
                    </div>
                    <div class="col-6 col-lg-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" @selected($status === 'active')>Active</option>
                            <option value="withdrawn" @selected($status === 'withdrawn')>Withdrawn</option>
                        </select>
                    </div>
                    <div class="col-3 col-lg-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-3 col-lg-auto">
                        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                @can('manage enrollments')
                    <a href="{{ route('admin.enrollments.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Enroll Student
                    </a>
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Batch</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $enrollment->student?->name }}</div>
                                    <div class="small text-muted">{{ $enrollment->student?->student_code }} | {{ $enrollment->student?->academicClass?->name }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $enrollment->batch?->name }}</div>
                                    <div class="small text-muted">
                                        {{ $enrollment->batch?->academicClass?->name }}
                                        @if ($enrollment->batch?->subject)
                                            | {{ $enrollment->batch?->subject?->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $enrollment->start_date?->format('d M Y') }}</td>
                                <td>{{ $enrollment->end_date?->format('d M Y') ?: '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $enrollment->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                                <td>{{ $enrollment->creator?->name ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @can('manage enrollments')
                                        @if ($enrollment->status === 'active')
                                            <a href="{{ route('admin.enrollments.withdraw.form', $enrollment) }}" class="btn btn-sm btn-outline-danger">Withdraw</a>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No enrollment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($enrollments->hasPages())
                <div class="mt-4">
                    {{ $enrollments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
