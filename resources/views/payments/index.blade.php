@extends('layouts.app')

@section('title', 'Payments')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Fee Collection</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Payment History</h1>
            <p class="text-secondary mb-0">Track receipt history with owner teacher and actual collector kept separately.</p>
        </div>
        @can('create', \App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Collect Fee</a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="admin-card p-4">
                    <div class="small text-secondary">Collected Today</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($todayTotal, 2) }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card p-4">
                    <div class="small text-secondary">Collected This Month</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($monthTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="admin-card p-3 p-lg-4 mb-4">
            <form method="GET" action="{{ route('payments.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-7">
                    <label for="q" class="form-label fw-semibold">Search</label>
                    <input id="q" type="text" name="q" class="form-control rounded-4" value="{{ request('q') }}" placeholder="Receipt no, student ID, student name, collector">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="payment_method" class="form-label fw-semibold">Method</label>
                    <select id="payment_method" name="payment_method" class="form-select rounded-4">
                        <option value="">All methods</option>
                        @foreach ($methods as $method)
                            <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ str($method)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary rounded-4">Filter</button>
                </div>
            </form>
        </div>

        <div class="admin-card p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Student</th>
                            <th>Owner Teacher</th>
                            <th>Collector</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $payment->receipt_no }}</div>
                                    <div class="small text-secondary">{{ optional($payment->collected_on)->format('Y-m-d H:i') }}</div>
                                </td>
                                <td>
                                    <div>{{ $payment->student?->name }}</div>
                                    <div class="small text-secondary">{{ $payment->student?->student_code }}</div>
                                </td>
                                <td>{{ $payment->ownerTeacher?->name ?? 'Not set' }}</td>
                                <td>
                                    <div>{{ $payment->collector?->name ?? 'System' }}</div>
                                    <div class="small text-secondary">{{ str($payment->collector_role)->replace('_', ' ')->title() }}</div>
                                </td>
                                <td>{{ str($payment->payment_method)->replace('_', ' ')->title() }}</td>
                                <td>{{ number_format((float) $payment->total_amount, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">No payments have been collected yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $payments->links() }}</div>
    </div>
@endsection
