@extends('layouts.admin')

@section('title', 'Payment Operations')
@section('page-title', 'Payment Operations')
@section('page-subtitle', 'Collect, review, and track payments.')

@section('content')
    <style>
        .payment-hub .summary-card {
            border: 0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            border-radius: 1rem;
        }

        .payment-hub .summary-card .card-body {
            padding: 1rem 1.1rem;
        }

        .payment-hub .summary-card .label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .payment-hub .summary-card .value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.15;
        }

        .payment-hub .compact-panel {
            border-radius: 1rem;
        }

        .payment-hub .compact-panel .card-body {
            padding: 1rem 1.1rem;
        }

        .payment-hub .due-group-card {
            border: 1px solid rgba(15, 23, 42, 0.07);
            border-radius: 0.95rem;
            padding: 0.95rem;
            height: 100%;
        }

        .payment-hub .due-group-card:hover {
            border-color: rgba(37, 99, 235, 0.18);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .payment-hub .pending-day-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.95rem;
            overflow: hidden;
        }

        .payment-hub .pending-day-card + .pending-day-card {
            margin-top: 0.9rem;
        }

        .payment-hub .pending-day-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            background: linear-gradient(180deg, #fafafa 0%, #f8fafc 100%);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }

        .payment-hub .pending-day-head .date-label {
            font-weight: 600;
            color: #0f172a;
        }

        .payment-hub .pending-day-head .count-label {
            font-size: 0.78rem;
            color: #64748b;
        }

        .payment-hub .history-table td,
        .payment-hub .history-table th {
            vertical-align: middle;
        }
    </style>

    <div class="payment-hub">
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                    <div class="card-body">
                        <div class="label text-primary">Today Approved</div>
                        <div class="value">{{ number_format($summaryCards['today_collected'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                    <div class="card-body">
                        <div class="label text-warning">Pending MFS</div>
                        <div class="value">{{ $summaryCards['pending_count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                    <div class="card-body">
                        <div class="label text-success">{{ $dashboardMonth }} Approved</div>
                        <div class="value">{{ number_format($summaryCards['month_approved'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
                    <div class="card-body">
                        <div class="label" style="color:#7c3aed;">{{ $dashboardMonth }} Due Students</div>
                        <div class="value">{{ $summaryCards['due_student_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-pills gap-2 mb-4" id="payment-ops-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'collect' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#collect-tab-pane" type="button" role="tab">Collect</button>
            </li>
            @can('approve payments')
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pending-tab-pane" type="button" role="tab">Pending</button>
                </li>
            @endcan
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'due' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#due-tab-pane" type="button" role="tab">Grouped Due</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'history' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#history-tab-pane" type="button" role="tab">History</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'collect' ? 'show active' : '' }}" id="collect-tab-pane" role="tabpanel">
                <div class="card page-card compact-panel mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-8">
                                <label for="quick-search" class="form-label">Find Student</label>
                                <form method="GET" action="{{ route('admin.payments.create') }}" class="input-group">
                                    <input type="text" id="quick-search" name="student_search" class="form-control" placeholder="Code, phone, guardian, name, or batch">
                                    <input type="month" name="month" value="{{ $dashboardMonth }}" class="form-control" style="max-width: 170px;">
                                    <button type="submit" class="btn btn-primary">Find</button>
                                </form>
                            </div>
                            <div class="col-xl-4">
                                <div class="d-grid gap-2 d-xl-flex justify-content-xl-end">
                                    <a href="{{ route('admin.payments.create') }}" class="btn btn-outline-primary">Collection Screen</a>
                                    <a href="{{ route('admin.payments.due-list') }}" class="btn btn-outline-secondary">Detailed Due List</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @can('approve payments')
                <div class="tab-pane fade {{ $activeTab === 'pending' ? 'show active' : '' }}" id="pending-tab-pane" role="tabpanel">
                    <div class="card page-card compact-panel mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 mb-0">Pending Approval</h2>
                                <a href="{{ route('admin.payments.index', ['status' => 'pending', 'tab' => 'history']) }}" class="btn btn-sm btn-outline-secondary">Open Full List</a>
                            </div>

                        @forelse ($pendingPayments as $date => $datePayments)
                            <div class="pending-day-card">
                                <div class="pending-day-head">
                                    <div class="date-label">{{ $date }}</div>
                                    <div class="count-label">{{ $datePayments->count() }} item{{ $datePayments->count() === 1 ? '' : 's' }}</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Student</th>
                                                <th>Batch</th>
                                                <th>Fee Item</th>
                                                <th>Method</th>
                                                <th>Txn ID</th>
                                                <th>Amount</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datePayments as $payment)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.student-profiles.show', $payment->enrollment->student) }}" class="fw-semibold text-decoration-none">
                                                            {{ $payment->enrollment->student?->name }}
                                                        </a>
                                                        <div class="small text-muted">{{ $payment->enrollment->student?->student_code }}</div>
                                                    </td>
                                                    <td>{{ $payment->enrollment->batch?->name }}</td>
                                                    <td>{{ $payment->batchFee?->feeType?->name ?: '-' }}</td>
                                                    <td>{{ strtoupper($payment->method) }}</td>
                                                    <td>{{ $payment->transaction_id ?: '-' }}</td>
                                                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="tab" value="pending">
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="tab" value="pending">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                                <div class="alert alert-success mb-0">No pending MFS payments right now.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endcan

            <div class="tab-pane fade {{ $activeTab === 'due' ? 'show active' : '' }}" id="due-tab-pane" role="tabpanel">
                <div class="card page-card compact-panel mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                            <div>
                                <h2 class="h5 mb-1">Grouped Due</h2>
                                <div class="text-muted small">By batch for {{ $dashboardMonth }}</div>
                            </div>
                            <a href="{{ route('admin.payments.due-list') }}" class="btn btn-outline-secondary">Open Detailed Due List</a>
                        </div>

                        <div class="row g-3">
                            @forelse ($dueGroups as $group)
                                <div class="col-xl-6">
                                    <div class="due-group-card">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                            <div>
                                                <div class="fw-semibold">{{ $group['batch_name'] }}</div>
                                                <div class="small text-muted">Class {{ $group['class_name'] }}</div>
                                            </div>
                                            <span class="badge text-bg-danger">{{ number_format($group['due_total'], 2) }}</span>
                                        </div>
                                        <div class="small text-muted mb-3">
                                            {{ $group['student_count'] }} students | {{ $group['fee_item_count'] }} due lines
                                        </div>
                                        <div class="small mb-3">
                                            @foreach ($group['top_fee_items'] as $feeName => $feeTotal)
                                                <div>{{ $feeName }}: <strong>{{ number_format($feeTotal, 2) }}</strong></div>
                                            @endforeach
                                        </div>
                                        <a href="{{ route('admin.payments.due-list', ['month' => $dashboardMonth, 'search' => $group['batch_name']]) }}" class="btn btn-sm btn-outline-primary">Open This Batch</a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-success mb-0">No grouped due records found for {{ $dashboardMonth }}.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'history' ? 'show active' : '' }}" id="history-tab-pane" role="tabpanel">
                <div class="card page-card compact-panel">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                            <div>
                                <h2 class="h5 mb-1">Payment History</h2>
                                <div class="text-muted small">Use filters to keep the list focused.</div>
                            </div>

                            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 w-100">
                                <input type="hidden" name="tab" value="history">
                                <div class="col-12 col-xl-4">
                                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Student, code, phone, batch, or transaction">
                                </div>
                                <div class="col-6 col-md-3 col-xl-2">
                                    <input type="month" name="month" value="{{ $month }}" class="form-control">
                                </div>
                                <div class="col-6 col-md-3 col-xl-2">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="pending" @selected($status === 'pending')>Pending</option>
                                        <option value="approved" @selected($status === 'approved')>Approved</option>
                                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 col-xl-2">
                                    <select name="method" class="form-select">
                                        <option value="">All Methods</option>
                                        <option value="cash" @selected($method === 'cash')>Cash</option>
                                        <option value="bkash" @selected($method === 'bkash')>bKash</option>
                                        <option value="nagad" @selected($method === 'nagad')>Nagad</option>
                                    </select>
                                </div>
                                <div class="col-3 col-md-auto">
                                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                                </div>
                                <div class="col-3 col-md-auto">
                                    <a href="{{ route('admin.payments.index', ['tab' => 'history']) }}" class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            </form>
                        </div>

                        @if ($historyHasFilters)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 history-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Batch</th>
                                            <th>Fee Item</th>
                                            <th>Month</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Transaction ID</th>
                                            <th>Payment Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payments as $payment)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.student-profiles.show', $payment->enrollment->student) }}" class="fw-semibold text-decoration-none">
                                                        {{ $payment->enrollment->student?->name }}
                                                    </a>
                                                    <div class="small text-muted">
                                                        {{ $payment->enrollment->student?->student_code }} | {{ $payment->enrollment->student?->guardian_phone }}
                                                    </div>
                                                </td>
                                                <td>{{ $payment->enrollment->batch?->name }}</td>
                                                <td>{{ $payment->batchFee?->feeType?->name ?: '-' }}</td>
                                                <td>{{ $payment->month ?: '-' }}</td>
                                                <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                                <td>{{ strtoupper($payment->method) }}</td>
                                                <td>
                                                    <span class="badge rounded-pill {{ $payment->status === 'approved' ? 'text-bg-success' : ($payment->status === 'pending' ? 'text-bg-warning' : 'text-bg-danger') }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $payment->transaction_id ?: '-' }}</td>
                                                <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                                <td class="text-end">
                                                    @if ($payment->status === 'pending' && auth()->user()->can('approve payments'))
                                                        <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="tab" value="history">
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="tab" value="history">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">No pending action</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5 text-muted">No payments found for the selected filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($payments->hasPages())
                                <div class="mt-4">{{ $payments->links() }}</div>
                            @endif
                        @else
                            <div class="py-5 text-center text-muted">
                                Use the filters above to open payment history.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('#payment-ops-tabs [data-bs-toggle="pill"]').forEach((button) => {
            button.addEventListener('shown.bs.tab', (event) => {
                const targetMap = {
                    '#collect-tab-pane': 'collect',
                    '#pending-tab-pane': 'pending',
                    '#due-tab-pane': 'due',
                    '#history-tab-pane': 'history',
                };

                const tab = targetMap[event.target.getAttribute('data-bs-target')];

                if (!tab) {
                    return;
                }

                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            });
        });
    </script>
@endpush
