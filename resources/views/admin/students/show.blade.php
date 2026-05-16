@extends('layouts.admin')

@section('title', 'Student Profile')
@section('page-title', 'Student Profile')
@section('page-subtitle', 'Student overview')

@push('styles')
    <style>
        .student-profile-photo {
            width: 104px;
            height: 104px;
            object-fit: cover;
        }

        .student-profile-meta dt {
            font-size: .78rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: .2rem;
        }

        .student-profile-meta dd {
            margin-bottom: .8rem;
            font-weight: 600;
        }

        .student-mini-stat {
            border: 1px solid #e5e7eb;
            border-radius: .85rem;
            padding: .9rem 1rem;
            background: #fff;
        }

        .student-mini-stat .label {
            font-size: .76rem;
            color: #6b7280;
            margin-bottom: .2rem;
        }

        .student-mini-stat .value {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .student-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .student-chip {
            display: inline-flex;
            align-items: center;
            padding: .36rem .62rem;
            border: 1px solid #dbe4f0;
            border-radius: 999px;
            background: #f8fafc;
            font-size: .78rem;
            color: #334155;
        }

        .student-section-title {
            font-size: .98rem;
            font-weight: 700;
        }

        .student-tabbar .nav-link {
            padding: .45rem .8rem;
            font-size: .84rem;
            border-radius: 999px;
        }

        .student-panel-block + .student-panel-block {
            margin-top: 1rem;
        }

        .student-compact-card {
            border: 1px solid #e5e7eb;
            border-radius: .9rem;
            background: #fff;
            padding: 1rem;
        }

        .student-compact-card .title {
            font-weight: 700;
        }

        .student-compact-card .sub {
            font-size: .8rem;
            color: #6b7280;
        }

        .student-compact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem 1rem;
        }

        .student-compact-grid .meta-label {
            font-size: .76rem;
            color: #6b7280;
        }

        .student-compact-grid .meta-value {
            font-weight: 600;
        }

        @media (max-width: 767.98px) {
            .student-compact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $activeEnrollments = $enrollments->where('status', 'active');
        $teacherNames = $enrollments
            ->flatMap(fn ($enrollment) => $enrollment->batch?->teachers?->pluck('user.name') ?? collect())
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        @if ($student->photoUrl())
                            <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="student-profile-photo rounded-circle border shadow-sm">
                        @else
                            <div class="student-profile-photo rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted">
                                No Photo
                            </div>
                        @endif

                        <div class="min-w-0 flex-grow-1">
                            <h2 class="h5 mb-1">{{ $student->name }}</h2>
                            <div class="small text-muted mb-2">{{ $student->student_code }}</div>

                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill {{ $student->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                                <span class="badge rounded-pill {{ $latestFaceRegistration && $latestFaceRegistration->status === 'verified' ? 'text-bg-success' : ($latestFaceRegistration && $latestFaceRegistration->status === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                    {{ $latestFaceRegistration ? ucfirst($latestFaceRegistration->status) : 'Face Not Ready' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <dl class="student-profile-meta mb-0">
                        <dt>Class</dt>
                        <dd>{{ $student->academicClass?->name ?: '-' }}</dd>

                        <dt>Student Mobile</dt>
                        <dd>{{ $student->phone ?: '-' }}</dd>

                        <dt>Guardian Mobile</dt>
                        <dd>{{ $student->guardian_phone ?: '-' }}</dd>

                        <dt>School</dt>
                        <dd>{{ $student->school ?: '-' }}</dd>

                        <dt>Address</dt>
                        <dd class="mb-0">{{ $student->address ?: '-' }}</dd>
                    </dl>

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
            <div class="card page-card student-panel-block">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                        <div>
                            <div class="student-section-title">This Month</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
                        </div>

                        <form method="GET" action="{{ route('admin.student-profiles.show', $student) }}" class="d-flex gap-2">
                            <input type="month" name="month" value="{{ $month }}" class="form-control">
                            <button type="submit" class="btn btn-outline-primary">Go</button>
                        </form>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6 col-xl-3">
                            <div class="student-mini-stat">
                                <div class="label">Active Batches</div>
                                <div class="value">{{ $profileSummary['active_enrollments'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="student-mini-stat">
                                <div class="label">Approved</div>
                                <div class="value">{{ number_format($profileSummary['month_approved'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="student-mini-stat">
                                <div class="label">Pending</div>
                                <div class="value">{{ number_format($profileSummary['month_pending'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="student-mini-stat">
                                <div class="label">Due</div>
                                <div class="value">{{ number_format($profileSummary['month_due'], 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-7">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="small text-muted mb-2">Current Batches</div>
                                @if ($activeEnrollments->isNotEmpty())
                                    <div class="student-chip-list">
                                        @foreach ($activeEnrollments as $enrollment)
                                            <span class="student-chip">{{ $enrollment->batch?->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small">No active batch.</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="small text-muted mb-2">Teachers</div>
                                <div class="small fw-semibold">{{ $teacherNames->isNotEmpty() ? $teacherNames->implode(', ') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="student-panel-block">
                <ul class="nav nav-pills gap-2 student-tabbar mb-3" role="tablist">
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
                                <div class="col-lg-6">
                                    <div class="student-compact-card h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div>
                                                <div class="title">{{ $enrollment->batch?->name }}</div>
                                                <div class="sub">
                                                    {{ $enrollment->batch?->academicClass?->name }}
                                                    @if ($enrollment->batch?->subject)
                                                        | {{ $enrollment->batch?->subject?->name }}
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill {{ $enrollment->status === 'active' ? 'text-bg-success' : ($enrollment->status === 'completed' ? 'text-bg-info' : 'text-bg-secondary') }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </div>

                                        <div class="sub mb-3">
                                            {{ $enrollment->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: '-' }}
                                        </div>

                                        <div class="student-compact-grid">
                                            <div>
                                                <div class="meta-label">Start</div>
                                                <div class="meta-value">{{ $enrollment->start_date?->format('d M Y') }}</div>
                                            </div>
                                            <div>
                                                <div class="meta-label">End</div>
                                                <div class="meta-value">{{ $enrollment->end_date?->format('d M Y') ?: '-' }}</div>
                                            </div>
                                            <div class="col-span-2">
                                                <div class="meta-label">Schedule</div>
                                                @php
                                                    $schedulePreview = collect($enrollment->batch?->schedule_entries ?? [])->take(2)->map(function ($entry) {
                                                        $dayLabel = match ($entry['day']) {
                                                            'sat' => 'Sat',
                                                            'sun' => 'Sun',
                                                            'mon' => 'Mon',
                                                            'tue' => 'Tue',
                                                            'wed' => 'Wed',
                                                            'thu' => 'Thu',
                                                            'fri' => 'Fri',
                                                            default => ucfirst((string) $entry['day']),
                                                        };

                                                        return $dayLabel.' '.$entry['start_time'].'-'.$entry['end_time'];
                                                    })->implode(', ');
                                                @endphp
                                                <div class="meta-value">{{ $schedulePreview ?: 'Not set' }}</div>
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
                                                    <td class="text-end">
                                                        {{ number_format($row['amount'], 2) }}
                                                        @if (($row['discount'] ?? 0) > 0)
                                                            <div class="small text-muted">
                                                                Base {{ number_format($row['base_amount'], 2) }} | Less {{ number_format($row['discount'], 2) }}
                                                            </div>
                                                        @endif
                                                    </td>
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
                        @if ($paymentGroups->isNotEmpty())
                            <div class="row g-2 mb-3">
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
                        @endif

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
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <div class="student-mini-stat">
                                    <div class="label">Present</div>
                                    <div class="value">{{ $profileSummary['attendance_present'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="student-mini-stat">
                                    <div class="label">Late</div>
                                    <div class="value">{{ $profileSummary['attendance_late'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="student-mini-stat">
                                    <div class="label">Absent</div>
                                    <div class="value">{{ $profileSummary['attendance_absent'] }}</div>
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
    </div>
@endsection
