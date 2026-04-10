@extends('layouts.app')

@section('title', 'Payment Receipt')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Collection</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Receipt {{ $payment->receipt_no }}</h1>
        <p class="text-secondary mb-0">Receipt view with owner teacher and collector details preserved separately.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="admin-card p-4 mb-4">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('payments.receipts.show', [$payment, 'printable']) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-3">Printable Receipt</a>
                <a href="{{ route('payments.receipts.show', [$payment, 'normal']) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3">Normal Print</a>
                <a href="{{ route('payments.receipts.show', [$payment, 'pos']) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3">POS Print</a>
            </div>
        </div>

        <div class="admin-card p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-4"><strong>Student:</strong> {{ $payment->student?->name }} ({{ $payment->student?->student_code }})</div>
                <div class="col-md-4"><strong>Owner Teacher:</strong> {{ $payment->ownerTeacher?->name }}</div>
                <div class="col-md-4"><strong>Collector:</strong> {{ $payment->collector?->name }} ({{ str($payment->collector_role)->replace('_', ' ')->title() }})</div>
                <div class="col-md-4"><strong>Method:</strong> {{ str($payment->payment_method)->replace('_', ' ')->title() }}</div>
                <div class="col-md-4"><strong>Collected On:</strong> {{ optional($payment->collected_on)->format('Y-m-d H:i') }}</div>
                <div class="col-md-4"><strong>Total:</strong> {{ number_format((float) $payment->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="admin-card p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Fee Head</th>
                            <th>Period</th>
                            <th>Charge</th>
                            <th>Due Before</th>
                            <th>Paid</th>
                            <th>Due After</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->items as $item)
                            <tr>
                                <td>{{ $item->feeHead?->name }}</td>
                                <td>{{ $item->billing_period_key }}</td>
                                <td>{{ number_format((float) $item->charge_amount, 2) }}</td>
                                <td>{{ number_format((float) $item->due_before, 2) }}</td>
                                <td>{{ number_format((float) $item->paid_amount, 2) }}</td>
                                <td>{{ number_format((float) $item->due_after, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($payment->postActions->isNotEmpty())
            <div class="admin-card p-3 p-lg-4 mt-4">
                <div class="page-section-title text-primary-emphasis mb-3">Post-Payment Actions</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle module-table mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Processed</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payment->postActions as $action)
                                <tr>
                                    <td>{{ str($action->action_type)->replace('_', ' ')->title() }}</td>
                                    <td>{{ str($action->status)->title() }}</td>
                                    <td>{{ $action->processed_at?->format('Y-m-d H:i') ?? 'Pending' }}</td>
                                    <td>{{ $action->error_message ?: ($action->result['reason'] ?? 'Completed') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
