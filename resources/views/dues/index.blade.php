@extends('layouts.app')

@section('title', 'Due Ledger')

@section('page_header')
    <div>
        <div class="page-section-title">Finance</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Due Ledger</h1>
        <p class="text-secondary mb-0">Generate monthly dues and review student-wise outstanding balances from one ledger source.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="admin-card p-4">
                    <div class="small text-secondary">Total Charge</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($summary['charge'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-card p-4">
                    <div class="small text-secondary">Collected Against Dues</div>
                    <div class="h4 fw-bold mb-0 text-success">{{ number_format($summary['paid'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-card p-4">
                    <div class="small text-secondary">Outstanding Due</div>
                    <div class="h4 fw-bold mb-0 text-danger">{{ number_format($summary['due'], 2) }}</div>
                </div>
            </div>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="admin-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div>
                        <div class="page-section-title text-primary-emphasis">Generate Monthly Dues</div>
                        <div class="small text-secondary">Monthly charges are safe to regenerate. Existing ledger rows will be refreshed, not duplicated.</div>
                    </div>
                </div>

                <form action="{{ route('dues.generate') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label for="generate_billing_period_key" class="form-label fw-semibold">Billing Period</label>
                        <input id="generate_billing_period_key" type="text" name="billing_period_key" class="form-control rounded-4" value="{{ old('billing_period_key', $defaultPeriodKey) }}" placeholder="2026-04" required>
                    </div>
                    <div class="col-md-5">
                        <label for="generate_student_id" class="form-label fw-semibold">Generate For Student</label>
                        <select id="generate_student_id" name="student_id" class="form-select rounded-4">
                            <option value="">All active students</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->name }} ({{ $student->student_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-dark rounded-4">Generate / Refresh</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="admin-card p-4">
            <form action="{{ route('dues.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="q" class="form-label fw-semibold">Search</label>
                    <input id="q" type="text" name="q" value="{{ request('q') }}" class="form-control rounded-4" placeholder="Student, fee head, batch, period">
                </div>
                <div class="col-md-3">
                    <label for="billing_period_key" class="form-label fw-semibold">Billing Period</label>
                    <input id="billing_period_key" type="text" name="billing_period_key" value="{{ request('billing_period_key') }}" class="form-control rounded-4" placeholder="2026-04">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select rounded-4">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <label class="form-label fw-semibold opacity-0">Apply</label>
                    <button type="submit" class="btn btn-outline-dark rounded-4">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Fee Head</th>
                            <th>Batch</th>
                            <th>Period</th>
                            <th>Charge</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dues as $due)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $due->student?->name }}</div>
                                    <div class="small text-secondary">{{ $due->student?->student_code }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $due->feeHead?->name }}</div>
                                    <div class="small text-secondary">{{ $due->feeStructure?->title }}</div>
                                </td>
                                <td>{{ $due->batch?->name ?? 'General' }}</td>
                                <td>
                                    <div>{{ $due->billing_period_key }}</div>
                                    <div class="small text-secondary">{{ str($due->billing_period_type)->replace('_', ' ')->title() }}</div>
                                </td>
                                <td>{{ number_format($due->charge_amount, 2) }}</td>
                                <td class="text-success">{{ number_format($due->paid_amount, 2) }}</td>
                                <td class="text-danger fw-semibold">{{ number_format($due->due_amount, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $due->status === \App\Models\StudentDue::STATUS_PAID ? 'text-bg-success' : ($due->status === \App\Models\StudentDue::STATUS_PARTIAL ? 'text-bg-warning' : 'text-bg-danger') }}">
                                        {{ str($due->status)->title() }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('dues.show', $due) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">View</a>
                                        <a href="{{ route('payments.create', ['student_lookup' => $due->student?->student_code, 'student_enrollment_id' => $due->student_enrollment_id, 'fee_structure_id' => $due->fee_structure_id, 'billing_period_type' => $due->billing_period_type, 'billing_period_key' => $due->billing_period_key]) }}" class="btn btn-sm btn-dark rounded-pill px-3">Collect</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-secondary">No due ledger rows found for the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $dues->links() }}
            </div>
        </div>
    </div>
@endsection
