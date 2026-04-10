@extends('layouts.app')

@section('title', 'Due Details')

@section('page_header')
    <div>
        <div class="page-section-title">Finance</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Due Details</h1>
        <p class="text-secondary mb-0">Review charge source, ownership, and linked payment history for one due row.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="admin-card p-4 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-secondary">Student</div>
                            <div class="fw-semibold">{{ $due->student?->name }}</div>
                            <div class="small text-secondary">{{ $due->student?->student_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-secondary">Owner Teacher</div>
                            <div class="fw-semibold">{{ $due->ownerTeacher?->name ?? 'Not set' }}</div>
                            <div class="small text-secondary">Collector may differ on each receipt.</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Fee Head</div>
                            <div class="fw-semibold">{{ $due->feeHead?->name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Structure</div>
                            <div class="fw-semibold">{{ $due->feeStructure?->title }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Batch</div>
                            <div class="fw-semibold">{{ $due->batch?->name ?? 'General' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Billing Period</div>
                            <div class="fw-semibold">{{ $due->billing_period_key }}</div>
                            <div class="small text-secondary">{{ str($due->billing_period_type)->replace('_', ' ')->title() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Period Range</div>
                            <div class="fw-semibold">{{ $due->period_start?->format('d M Y') ?? 'N/A' }}</div>
                            <div class="small text-secondary">{{ $due->period_end?->format('d M Y') ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Status</div>
                            <div class="fw-semibold">{{ str($due->status)->title() }}</div>
                            <div class="small text-secondary">Synced {{ $due->last_synced_at?->diffForHumans() ?? 'Not yet' }}</div>
                        </div>
                    </div>
                </div>

                <div class="admin-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="page-section-title text-success-emphasis">Linked Payment History</div>
                        <a href="{{ route('payments.create', ['student_lookup' => $due->student?->student_code, 'student_enrollment_id' => $due->student_enrollment_id, 'fee_structure_id' => $due->fee_structure_id, 'billing_period_type' => $due->billing_period_type, 'billing_period_key' => $due->billing_period_key]) }}" class="btn btn-sm btn-dark rounded-pill px-3">Collect Against This Due</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle module-table mb-0">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Collector</th>
                                    <th>Date</th>
                                    <th>Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($due->paymentItems as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->payment?->receipt_no }}</div>
                                            <div class="small text-secondary">{{ $item->payment?->payment_method ? str($item->payment->payment_method)->replace('_', ' ')->title() : '' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $item->payment?->collector?->name ?? 'Unknown' }}</div>
                                            <div class="small text-secondary">{{ str($item->payment?->collector_role ?? 'user')->replace('_', ' ')->title() }}</div>
                                        </td>
                                        <td>{{ $item->payment?->collected_on?->format('d M Y h:i A') }}</td>
                                        <td class="text-success fw-semibold">{{ number_format($item->paid_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">No payments have been linked to this due row yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-card p-4 mb-4">
                    <div class="small text-secondary">Charge Amount</div>
                    <div class="h3 fw-bold mb-0">{{ number_format($due->charge_amount, 2) }}</div>
                </div>
                <div class="admin-card p-4 mb-4">
                    <div class="small text-secondary">Paid So Far</div>
                    <div class="h3 fw-bold text-success mb-0">{{ number_format($due->paid_amount, 2) }}</div>
                </div>
                <div class="admin-card p-4">
                    <div class="small text-secondary">Outstanding Due</div>
                    <div class="h3 fw-bold text-danger mb-0">{{ number_format($due->due_amount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
