@extends('layouts.admin')

@section('title', 'Student Profile')
@section('page-title', 'Student Profile')
@section('page-subtitle', 'Student overview')

@section('content')
    @php
        $activeEnrollments = $enrollments->where('status', 'active');
        $teacherNames = $enrollments
            ->flatMap(fn ($enrollment) => $enrollment->batch?->teachers?->pluck('user.name') ?? collect())
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4 text-center">
                    @if ($student->photoUrl())
                        <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="rounded-circle border shadow-sm mb-3" style="width: 128px; height: 128px; object-fit: cover;">
                    @else
                        <div class="rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted mb-3" style="width: 128px; height: 128px;">
                            No Photo
                        </div>
                    @endif

                    <h2 class="h4 mb-1">{{ $student->name }}</h2>
                    <div class="text-muted mb-3">{{ $student->student_code }}</div>

                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                        <span class="badge rounded-pill {{ $student->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ ucfirst($student->status) }}
                        </span>
                        <span class="badge rounded-pill {{ $latestFaceRegistration && $latestFaceRegistration->status === 'verified' ? 'text-bg-success' : ($latestFaceRegistration && $latestFaceRegistration->status === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                            {{ $latestFaceRegistration ? ucfirst($latestFaceRegistration->status) : 'Face Not Ready' }}
                        </span>
                    </div>

                    <div class="text-start">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="text-muted small">Class</div>
                                <div class="fw-semibold">{{ $student->academicClass?->name ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Student Mobile</div>
                                <div class="fw-semibold">{{ $student->phone ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Guardian Mobile</div>
                                <div class="fw-semibold">{{ $student->guardian_phone ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">School</div>
                                <div class="fw-semibold">{{ $student->school ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Address</div>
                                <div class="fw-semibold">{{ $student->address ?: '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        @can('collect payments')
                            <a href="{{ route('admin.payments.create', ['student' => $student->id, 'month' => $month]) }}" class="btn btn-primary">Collect Payment</a>
                        @endcan
                        @can('manage students')
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-primary">Edit Student</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <h3 class="h5 mb-1">Summary</h3>
                            <div class="small text-muted">{{ $month }}</div>
                        </div>

                        <form method="GET" action="{{ route('admin.student-profiles.show', $student) }}" class="d-flex gap-2">
                            <input type="month" name="month" value="{{ $month }}" class="form-control">
                            <button type="submit" class="btn btn-outline-primary">Go</button>
                        </form>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 bg-primary-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Active Batches</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ $profileSummary['active_enrollments'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 bg-success-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Approved</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ number_format($profileSummary['month_approved'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 bg-warning-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Pending</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ number_format($profileSummary['month_pending'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card border-0 bg-danger-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Due</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ number_format($profileSummary['month_due'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small mb-2">Current Batches</div>
                                @if ($activeEnrollments->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($activeEnrollments as $enrollment)
                                            <span class="badge text-bg-primary">{{ $enrollment->batch?->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">No active batch</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small mb-2">Teachers</div>
                                <div class="fw-semibold">{{ $teacherNames->isNotEmpty() ? $teacherNames->implode(', ') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#student-batches-tab" type="button" role="tab">Batches</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-fees-tab" type="button" role="tab">Fees</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-payments-tab" type="button" role="tab">Payments</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-attendance-tab" type="button" role="tab">Attendance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-admissions-tab" type="button" role="tab">Admissions</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="student-batches-tab" role="tabpanel">
                    <div class="row g-3">
                        @forelse ($enrollments as $enrollment)
                            <div class="col-xl-6">
                                <div class="card page-card h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between gap-3 mb-2">
                                            <div>
                                                <div class="fw-semibold">{{ $enrollment->batch?->name }}</div>
                                                <div class="small text-muted">
                                                    {{ $enrollment->batch?->academicClass?->name }}
                                                    @if ($enrollment->batch?->subject)
                                                        | {{ $enrollment->batch?->subject?->name }}
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill {{ $enrollment->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            {{ $enrollment->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: '-' }}
                                        </div>

                                        <div class="small text-muted mb-3">
                                            @php
                                                $days = collect($enrollment->batch?->schedule_days ?? [])->map(fn ($day) => ucfirst($day))->implode(', ');
                                            @endphp
                                            {{ $days ?: 'Schedule not set' }}
                                            @if ($enrollment->batch?->start_time && $enrollment->batch?->end_time)
                                                | {{ $enrollment->batch->start_time->format('h:i A') }} - {{ $enrollment->batch->end_time->format('h:i A') }}
                                            @endif
                                        </div>

                                        <div class="row g-2 small">
                                            <div class="col-6">
                                                <div class="text-muted">Start</div>
                                                <div class="fw-semibold">{{ $enrollment->start_date?->format('d M Y') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted">End</div>
                                                <div class="fw-semibold">{{ $enrollment->end_date?->format('d M Y') ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card page-card">
                                    <div class="card-body py-5 text-center text-muted">No batch found.</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="tab-pane fade" id="student-fees-tab" role="tabpanel">
                    <div class="card page-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Batch</th>
                                            <th>Fee</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Approved</th>
                                            <th class="text-end">Pending</th>
                                            <th class="text-end">Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($feeSummaryRows as $row)
                                            <tr>
                                                <td>{{ $row['batch_name'] }}</td>
                                                <td>{{ $row['fee_name'] }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $row['frequency'] ?? '-')) }}</td>
                                                <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                                <td class="text-end">{{ number_format($row['approved'], 2) }}</td>
                                                <td class="text-end">{{ number_format($row['pending'], 2) }}</td>
                                                <td class="text-end fw-semibold {{ $row['remaining'] > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($row['remaining'], 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">No fee data found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="student-payments-tab" role="tabpanel">
                    <div class="row g-3 mb-3">
                        @foreach ($paymentGroups as $group)
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="fw-semibold">{{ $group['batch_name'] }}</div>
                                    <div class="small text-muted mb-2">{{ $group['count'] }} entries</div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Approved</span>
                                        <strong>{{ number_format($group['approved'], 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Pending</span>
                                        <strong>{{ number_format($group['pending'], 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card page-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Batch</th>
                                            <th>Fee</th>
                                            <th>Month</th>
                                            <th>Method</th>
                                            <th>Collected By</th>
                                            <th>Status</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($paymentHistory as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</td>
                                                <td>{{ $payment->enrollment?->batch?->name ?: '-' }}</td>
                                                <td>{{ $payment->batchFee?->feeType?->name ?: '-' }}</td>
                                                <td>{{ $payment->month ?: '-' }}</td>
                                                <td>{{ strtoupper($payment->method) }}</td>
                                                <td>{{ $payment->collector?->name ?: '-' }}</td>
                                                <td>
                                                    <span class="badge rounded-pill {{ $payment->status === 'approved' ? 'text-bg-success' : ($payment->status === 'pending' ? 'text-bg-warning' : 'text-bg-danger') }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">No payment found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="student-attendance-tab" role="tabpanel">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="card border-0 bg-success-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Present</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ $profileSummary['attendance_present'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-info-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Late</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ $profileSummary['attendance_late'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-danger-subtle h-100">
                                <div class="card-body">
                                    <div class="text-muted small">Absent</div>
                                    <div class="fs-3 fw-semibold mt-2">{{ $profileSummary['attendance_absent'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card page-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Batch</th>
                                            <th>Status</th>
                                            <th>Method</th>
                                            <th>Marked By</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($attendanceHistory as $attendanceRow)
                                            <tr>
                                                <td>{{ $attendanceRow->session?->attendance_date?->format('d M Y') ?: '-' }}</td>
                                                <td>{{ $attendanceRow->session?->batch?->name ?: '-' }}</td>
                                                <td>
                                                    <span class="badge rounded-pill {{ $attendanceRow->status === 'present' ? 'text-bg-success' : ($attendanceRow->status === 'late' ? 'text-bg-info' : ($attendanceRow->status === 'absent' ? 'text-bg-danger' : ($attendanceRow->status === 'excused' ? 'text-bg-secondary' : 'text-bg-warning'))) }}">
                                                        {{ ucfirst($attendanceRow->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-capitalize">{{ $attendanceRow->method ?: '-' }}</td>
                                                <td>{{ $attendanceRow->marker?->name ?: '-' }}</td>
                                                <td>{{ $attendanceRow->marked_at?->format('d M h:i A') ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">No attendance found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="student-admissions-tab" role="tabpanel">
                    <div class="card page-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Batch</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Reviewed By</th>
                                            <th>Reviewed At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($student->admissionRequests as $requestRow)
                                            <tr>
                                                <td>{{ $requestRow->batch?->name ?: '-' }}</td>
                                                <td>{{ $requestRow->name }}</td>
                                                <td>{{ $requestRow->phone ?: $requestRow->guardian_phone ?: '-' }}</td>
                                                <td>
                                                    <span class="badge rounded-pill {{ $requestRow->status === 'approved' ? 'text-bg-success' : ($requestRow->status === 'pending' ? 'text-bg-warning' : 'text-bg-danger') }}">
                                                        {{ ucfirst($requestRow->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $requestRow->reviewer?->name ?: '-' }}</td>
                                                <td>{{ $requestRow->reviewed_at?->format('d M Y h:i A') ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">No admission history found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
