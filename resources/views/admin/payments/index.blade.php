@extends('layouts.admin')

@section('title', 'Payment Operations')
@section('page-title', 'Payment Operations')
@section('page-subtitle', 'Collect faster, review pending MFS, follow up grouped dues, and open full history only when needed.')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Today Approved Collection</div>
                    <div class="h3 mb-1">{{ number_format($summaryCards['today_collected'], 2) }}</div>
                    <div class="small text-muted">Based on approved payments dated today</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Pending MFS</div>
                    <div class="h3 mb-1">{{ $summaryCards['pending_count'] }}</div>
                    <div class="small text-muted">bKash and Nagad items waiting for approval</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">{{ $dashboardMonth }} Approved</div>
                    <div class="h3 mb-1">{{ number_format($summaryCards['month_approved'], 2) }}</div>
                    <div class="small text-muted">Approved by payment date in the selected month</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">{{ $dashboardMonth }} Due Students</div>
                    <div class="h3 mb-1">{{ $summaryCards['due_student_count'] }}</div>
                    <div class="small text-muted">Students still carrying grouped dues this month</div>
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
                <button class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pending-tab-pane" type="button" role="tab">Pending Approval</button>
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
            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-8">
                            <label for="quick-search" class="form-label">Quick Student Search</label>
                            <form method="GET" action="{{ route('admin.payments.create') }}" class="input-group">
                                <input type="text" id="quick-search" name="student_search" class="form-control" placeholder="Type student code, phone, guardian phone, name, or batch keyword">
                                <input type="month" name="month" value="{{ $dashboardMonth }}" class="form-control" style="max-width: 180px;">
                                <button type="submit" class="btn btn-primary">Find & Collect</button>
                            </form>
                            <div class="form-text">Example: entering <strong>003</strong> should bring matching student code or phone patterns quickly.</div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-grid gap-2 d-lg-flex justify-content-lg-end">
                                <a href="{{ route('admin.payments.create') }}" class="btn btn-outline-primary">Open Collection Screen</a>
                                <a href="{{ route('admin.payments.due-list', ['month' => $dashboardMonth]) }}" class="btn btn-outline-secondary">Monthly Due List</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('approve payments')
            <div class="tab-pane fade {{ $activeTab === 'pending' ? 'show active' : '' }}" id="pending-tab-pane" role="tabpanel">
                <div class="card page-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 mb-0">Pending MFS Approval Queue</h2>
                            <a href="{{ route('admin.payments.index', ['status' => 'pending', 'tab' => 'history']) }}" class="btn btn-sm btn-outline-secondary">Open Full Pending History</a>
                        </div>

                        @forelse ($pendingPayments as $date => $datePayments)
                            <div class="border rounded-3 mb-3 overflow-hidden">
                                <div class="bg-light px-3 py-2 border-bottom">
                                    <div class="fw-semibold">{{ $date }}</div>
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
        </div>

        <div class="tab-pane fade {{ $activeTab === 'due' ? 'show active' : '' }}" id="due-tab-pane" role="tabpanel">
            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Grouped Due Follow-Up</h2>
                            <div class="text-muted small">Grouped by batch for {{ $dashboardMonth }} so accounts can follow up operationally instead of scanning a long table.</div>
                        </div>
                        <a href="{{ route('admin.payments.due-list', ['month' => $dashboardMonth]) }}" class="btn btn-outline-secondary">Open Detailed Due List</a>
                    </div>

                    <div class="row g-3">
                        @forelse ($dueGroups as $group)
                            <div class="col-xl-6">
                                <div class="border rounded-3 h-100 p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <div>
                                            <div class="fw-semibold">{{ $group['batch_name'] }}</div>
                                            <div class="small text-muted">Class {{ $group['class_name'] }}</div>
                                        </div>
                                        <span class="badge text-bg-danger">{{ number_format($group['due_total'], 2) }}</span>
                                    </div>
                                    <div class="small text-muted mb-3">
                                        {{ $group['student_count'] }} students | {{ $group['fee_item_count'] }} due fee lines
                                    </div>
                                    <div class="small mb-3">
                                        @foreach ($group['top_fee_items'] as $feeName => $feeTotal)
                                            <div>{{ $feeName }}: <strong>{{ number_format($feeTotal, 2) }}</strong></div>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('admin.payments.due-list', ['month' => $dashboardMonth, 'search' => $group['batch_name']]) }}" class="btn btn-sm btn-outline-primary">Open This Batch Due</a>
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
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                        <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 w-100">
                            <input type="hidden" name="tab" value="history">
                            <div class="col-12 col-xl-4">
                                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student, phone, code, batch, or transaction ID">
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
                                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
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
                                        <td colspan="10" class="text-center py-5 text-muted">No payments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($payments->hasPages())
                        <div class="mt-4">{{ $payments->links() }}</div>
                    @endif
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
