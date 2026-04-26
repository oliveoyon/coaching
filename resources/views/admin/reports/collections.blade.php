@extends('layouts.admin')

@section('title', 'Collection Reports')
@section('page-title', 'Collection Reports')
@section('page-subtitle', 'Daily summary, batch-wise, teacher-wise, and detailed approved collection history.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.collections') }}" class="row g-3 align-items-end">
                <div class="col-lg-2">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                </div>
                <div class="col-lg-3">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classOptions as $class)
                            <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="batch_id" class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select">
                        <option value="">All Batches</option>
                        @foreach ($batchOptions as $batch)
                            <option value="{{ $batch->id }}" @selected((string) $batchId === (string) $batch->id)>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="teacher_id" class="form-label">Teacher</label>
                    <select name="teacher_id" id="teacher_id" class="form-select" @disabled($teacherScopeId)>
                        <option value="">All Teachers</option>
                        @foreach ($teacherOptions as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) $selectedTeacherId === (string) $teacher->id)>{{ $teacher->user?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Daily Collection</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Date</th><th>Count</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($monthlyCollection as $row)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::parse($row->report_date)->format('d M') }}</td>
                                        <td>{{ $row->payment_count }}</td>
                                        <td class="text-end">{{ number_format((float) $row->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No collection found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Batch-wise Collection</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Batch</th><th>Count</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($batchWiseCollection as $row)
                                    <tr>
                                        <td>{{ $row->batch_name }}</td>
                                        <td>{{ $row->payment_count }}</td>
                                        <td class="text-end">{{ number_format((float) $row->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No batch summary found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Teacher-wise Collection</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Teacher</th><th>Count</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($teacherWiseCollection as $row)
                                    <tr>
                                        <td>{{ $row->teacher_name }}</td>
                                        <td>{{ $row->payment_count }}</td>
                                        <td class="text-end">{{ number_format((float) $row->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No teacher summary found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                            <th>Student</th>
                            <th>Batch</th>
                            <th>Fee Head</th>
                            <th>Method</th>
                            <th>Collected By</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paymentHistory as $payment)
                            <tr>
                                <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $payment->enrollment?->student?->name }}</div>
                                    <div class="text-muted small">{{ $payment->enrollment?->student?->student_code }}</div>
                                </td>
                                <td>{{ $payment->enrollment?->batch?->name }}</td>
                                <td>{{ $payment->batchFee?->feeType?->name ?: '-' }}</td>
                                <td>{{ strtoupper($payment->method) }}</td>
                                <td>{{ $payment->collector?->name ?: '-' }}</td>
                                <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No approved collection rows found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($paymentHistory->hasPages())
            <div class="card-footer bg-white">
                {{ $paymentHistory->links() }}
            </div>
        @endif
    </div>
@endsection
