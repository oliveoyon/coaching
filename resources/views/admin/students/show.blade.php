@extends('layouts.admin')

@section('title', 'Student Panel')
@section('page-title', 'Student Panel')
@section('page-subtitle', 'A clear single-page view of this student: profile, batches, dues, payments, and current study information.')

@section('content')
    <div class="card page-card mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-xl-4 border-end">
                    <div class="p-4 h-100" style="background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);">
                        <div class="text-center mb-4">
                            @if ($student->photoUrl())
                                <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="rounded-circle border shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                            @else
                                <div class="rounded-circle border d-inline-flex align-items-center justify-content-center bg-light text-muted shadow-sm" style="width: 140px; height: 140px;">
                                    No Photo
                                </div>
                            @endif
                        </div>

                        <div class="text-center mb-4">
                            <h2 class="h4 mb-1">{{ $student->name }}</h2>
                            <div class="text-muted">{{ $student->student_code }}</div>
                            <span class="badge rounded-pill mt-2 {{ $student->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="text-muted small">Class</div>
                                <div class="fw-semibold">{{ $student->academicClass?->name ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Student WhatsApp / Mobile</div>
                                <div class="fw-semibold">{{ $student->phone ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Guardian WhatsApp / Mobile</div>
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
                </div>

                <div class="col-xl-8">
                    <div class="p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                            <div>
                                <h3 class="h5 mb-1">Operational Summary</h3>
                                <div class="text-muted small">Tracking month: {{ $month }}</div>
                            </div>

                            <form method="GET" action="{{ route('admin.student-profiles.show', $student) }}" class="d-flex gap-2">
                                <input type="month" name="month" value="{{ $month }}" class="form-control">
                                <button type="submit" class="btn btn-outline-primary">Change Month</button>
                            </form>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 col-xl-3">
                                <div class="card metric-card bg-primary-subtle border-0 h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">Current Batches</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ $profileSummary['active_enrollments'] }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="card metric-card bg-success-subtle border-0 h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">{{ $month }} Approved</div>
                                        <div class="text-muted small">Paid in This Month</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ number_format($profileSummary['month_approved'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="card metric-card bg-warning-subtle border-0 h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">{{ $month }} Pending</div>
                                        <div class="text-muted small">Waiting for Approval</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ number_format($profileSummary['month_pending'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="card metric-card bg-danger-subtle border-0 h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">Remaining for This Month</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ number_format($profileSummary['month_due'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @can('collect payments')
                                <a href="{{ route('admin.payments.create', ['student' => $student->id, 'month' => $month]) }}" class="btn btn-primary">Collect Payment</a>
                            @endcan
                            @can('manage students')
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-primary">Edit Profile</a>
                            @endcan
                            @can('manage enrollments')
                                <a href="{{ route('admin.enrollments.create') }}" class="btn btn-outline-secondary">New Enrollment</a>
                            @endcan
                            @canany(['collect payments', 'approve payments'])
                                <a href="{{ route('admin.payments.index', ['search' => $student->student_code, 'tab' => 'history']) }}" class="btn btn-outline-secondary">Payment History</a>
                            @endcanany
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small mb-2">Current Batch Names</div>
                        @if ($enrollments->where('status', 'active')->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($enrollments->where('status', 'active') as $enrollment)
                                    <span class="badge text-bg-primary">{{ $enrollment->batch?->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">No active batch right now.</div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small mb-2">Main Teachers</div>
                        @php
                            $teacherNames = $enrollments->flatMap(fn ($enrollment) => $enrollment->batch?->teachers?->pluck('user.name') ?? collect())->filter()->unique()->values();
                        @endphp
                        @if ($teacherNames->isNotEmpty())
                            <div class="fw-semibold">{{ $teacherNames->implode(', ') }}</div>
                        @else
                            <div class="text-muted">No teacher assigned.</div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small mb-2">Quick Reading</div>
                        <div class="small">
                            @if ($profileSummary['month_due'] > 0)
                                This student still has dues for {{ $month }}.
                            @elseif ($profileSummary['month_pending'] > 0)
                                Payments are submitted and waiting for approval.
                            @else
                                No visible due for {{ $month }} in the current view.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="text-muted small mb-2">Study Journey</div>
                        <div class="small mb-1">Total Batches Joined: <strong>{{ $profileSummary['total_batches'] }}</strong></div>
                        <div class="small mb-1">Withdrawn Batches: <strong>{{ $profileSummary['withdrawn_enrollments'] }}</strong></div>
                        <div class="small">First Joined: <strong>{{ $profileSummary['first_joined_at']?->format('d M Y') ?: '-' }}</strong></div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#student-overview-tab" type="button" role="tab">Summary</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-fee-tab" type="button" role="tab">Fees & Dues</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-history-tab" type="button" role="tab">Payment History</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#student-admission-tab" type="button" role="tab">Admission History</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="student-overview-tab" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-xl-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 mb-0">Study Overview</h2>
                                <span class="text-muted small">{{ $enrollments->count() }} visible batch records</span>
                            </div>
                            <div class="row g-3">
                                @forelse ($enrollments as $enrollment)
                                    <div class="col-xl-6">
                                        <div class="border rounded-3 h-100 p-3">
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
                                                Teachers: {{ $enrollment->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: 'Not set' }}
                                            </div>

                                            <div class="small text-muted mb-3">
                                                Schedule:
                                                @php
                                                    $days = collect($enrollment->batch?->schedule_days ?? [])->map(fn ($day) => ucfirst($day))->implode(', ');
                                                @endphp
                                                {{ $days ?: 'Not set' }}
                                                @if ($enrollment->batch?->start_time && $enrollment->batch?->end_time)
                                                    | {{ $enrollment->batch->start_time->format('h:i A') }} - {{ $enrollment->batch->end_time->format('h:i A') }}
                                                @endif
                                            </div>

                                            <div class="row g-2 small">
                                                <div class="col-6">
                                                    <div class="text-muted">Start Date</div>
                                                    <div class="fw-semibold">{{ $enrollment->start_date?->format('d M Y') }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted">End Date</div>
                                                    <div class="fw-semibold">{{ $enrollment->end_date?->format('d M Y') ?: '-' }}</div>
                                                </div>
                                            </div>

                                            @if ($enrollment->status === 'active' && auth()->user()->can('collect payments'))
                                                <div class="mt-3">
                                                    <a href="{{ route('admin.payments.create', ['student' => $student->id, 'batch' => $enrollment->batch_id, 'month' => $month]) }}" class="btn btn-sm btn-outline-success">Collect For This Batch</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-light border mb-0">No enrollments found for this student.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="border rounded-3 p-3 h-100">
                                <h2 class="h6 mb-3">Payment Snapshot</h2>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Payment Rows</span>
                                    <strong>{{ $profileSummary['payment_count'] }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Lifetime Approved</span>
                                    <strong>{{ number_format($profileSummary['total_approved'], 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Current Pending</span>
                                    <strong>{{ number_format($profileSummary['total_pending'], 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">{{ $month }} Due</span>
                                    <strong class="{{ $profileSummary['month_due'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($profileSummary['month_due'], 2) }}</strong>
                                </div>
                                <hr>
                                <div class="small text-muted mb-1">What this means</div>
                                <div class="small">
                                    Approved means fully accepted payment. Pending means submitted but not yet approved. Remaining means what is still unpaid in the selected month view.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="student-fee-tab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Fees and Dues</h2>
                        <span class="text-muted small">Month {{ $month }}</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch</th>
                                    <th>Fee Head</th>
                                    <th>Frequency</th>
                                    <th class="text-end">Fee</th>
                                    <th class="text-end">Approved</th>
                                    <th class="text-end">Pending</th>
                                    <th class="text-end">Remaining</th>
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
                                        <td colspan="7" class="text-center py-4 text-muted">No fee setup found for visible active enrollments.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="student-history-tab" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-xl-4">
                            <div class="border rounded-3 p-3 h-100">
                                <h2 class="h6 mb-3">Batch-wise Payment Summary</h2>
                                @forelse ($paymentGroups as $group)
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="fw-semibold">{{ $group['batch_name'] }}</div>
                                        <div class="small text-muted mb-2">
                                            {{ $group['count'] }} payment rows
                                            @if ($group['last_payment_date'])
                                                | Last: {{ $group['last_payment_date']->format('d M Y') }}
                                            @endif
                                        </div>
                                        <div class="small d-flex justify-content-between mb-1">
                                            <span>Approved</span>
                                            <strong>{{ number_format($group['approved'], 2) }}</strong>
                                        </div>
                                        <div class="small d-flex justify-content-between">
                                            <span>Pending</span>
                                            <strong>{{ number_format($group['pending'], 2) }}</strong>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No batch-wise payment summary available.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h2 class="h5 mb-0">Payment History</h2>
                                    <div class="small text-muted">Showing up to the latest 50 visible payment entries for this student.</div>
                                </div>
                                @canany(['collect payments', 'approve payments'])
                                    <a href="{{ route('admin.payments.index', ['search' => $student->student_code, 'tab' => 'history']) }}" class="btn btn-sm btn-outline-secondary">Open Full Payment Workspace</a>
                                @endcanany
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Batch</th>
                                            <th>Fee Head</th>
                                            <th>Month</th>
                                            <th>Method</th>
                                            <th>Collected By</th>
                                            <th>Approved By</th>
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
                                                <td>{{ $payment->approver?->name ?: '-' }}</td>
                                                <td>
                                                    <span class="badge rounded-pill {{ $payment->status === 'approved' ? 'text-bg-success' : ($payment->status === 'pending' ? 'text-bg-warning' : 'text-bg-danger') }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4 text-muted">No visible payment history found for this student.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="student-admission-tab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-0">Admission History</h2>
                            <div class="small text-muted">Public admission requests and review history linked with this student.</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch</th>
                                    <th>Submitted Student</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Reviewed By</th>
                                    <th>Reviewed At</th>
                                    <th>Note</th>
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
                                        <td>{{ $requestRow->review_note ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No admission request history is linked with this student yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
