@extends('layouts.admin')

@section('title', 'Enrollment Details')
@section('page-title', 'Enrollment Details')
@section('page-subtitle', 'Review batch participation history and current withdrawal status.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-4">Enrollment Summary</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Student</div>
                            <div class="fw-semibold">{{ $enrollment->student?->name }}</div>
                            <div class="small text-muted">{{ $enrollment->student?->student_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Batch</div>
                            <div class="fw-semibold">{{ $enrollment->batch?->name }}</div>
                            <div class="small text-muted">{{ $enrollment->batch?->academicClass?->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Start Date</div>
                            <div class="fw-semibold">{{ $enrollment->start_date?->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">End Date</div>
                            <div class="fw-semibold">{{ $enrollment->end_date?->format('d M Y') ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Status</div>
                            <span class="badge rounded-pill {{ $enrollment->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Created By</div>
                            <div class="fw-semibold">{{ $enrollment->creator?->name ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Batch Teachers</h2>
                    <div class="d-flex flex-column gap-2">
                        @foreach ($enrollment->batch?->teachers ?? [] as $teacher)
                            <div class="border rounded-3 px-3 py-2">
                                <div class="fw-semibold">{{ $teacher->user?->name }}</div>
                                <div class="small text-muted">{{ $teacher->user?->email }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card page-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Student Contact</h2>
                    <div class="small text-muted mb-1">Guardian Phone</div>
                    <div class="fw-semibold mb-3">{{ $enrollment->student?->guardian_phone }}</div>

                    <div class="small text-muted mb-1">School</div>
                    <div class="fw-semibold mb-3">{{ $enrollment->student?->school ?: '-' }}</div>

                    <div class="small text-muted mb-1">Address</div>
                    <div class="fw-semibold">{{ $enrollment->student?->address ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
