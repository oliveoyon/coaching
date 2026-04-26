@extends('layouts.admin')

@section('title', 'Due List')
@section('page-title', 'Due List By Month')
@section('page-subtitle', 'Only active enrollments in the selected month are included. Withdrawn enrollments do not generate future dues.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.payments.due-list') }}" class="row g-2 w-100">
                    <div class="col-12 col-md-4">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student, phone, code, or batch">
                    </div>
                    <div class="col-6 col-md-3">
                        <input type="month" name="month" value="{{ $month }}" class="form-control" required>
                    </div>
                    <div class="col-3 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-3 col-md-auto">
                        <a href="{{ route('admin.payments.due-list') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-outline-secondary">Pending MFS</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Batch</th>
                            <th>Fee Item</th>
                            <th>Approved</th>
                            <th>Pending</th>
                            <th>Remaining</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dueEnrollments as $enrollment)
                            @foreach ($enrollment->batch?->batchFees?->where('status', 'active') ?? [] as $batchFee)
                                @php($isMonthly = $batchFee->feeType?->frequency === 'monthly')
                                @php($summary = app(\App\Http\Controllers\Admin\PaymentController::class)->feeSummary($enrollment, $batchFee, $isMonthly ? $month : null))
                                @continue($summary['remaining'] <= 0 && $summary['approved'] <= 0 && $summary['pending'] <= 0 && $batchFee->feeType?->frequency !== 'monthly')
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.student-profiles.show', $enrollment->student) }}" class="fw-semibold text-decoration-none">
                                            {{ $enrollment->student?->name }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $enrollment->student?->student_code }} | {{ $enrollment->student?->guardian_phone }}
                                        </div>
                                    </td>
                                    <td>{{ $enrollment->batch?->name }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $batchFee->feeType?->name }}</div>
                                        <div class="small text-muted">
                                            {{ ucfirst(str_replace('_', ' ', $batchFee->feeType?->frequency ?? '')) }}
                                            @if ($isMonthly)
                                                | {{ $month }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ number_format($summary['approved'], 2) }}</td>
                                    <td>{{ number_format($summary['pending'], 2) }}</td>
                                    <td>
                                        <span class="fw-semibold {{ $summary['remaining'] > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($summary['remaining'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if ($summary['remaining'] > 0)
                                            <a href="{{ route('admin.payments.create', ['student' => $enrollment->student_id, 'batch' => $enrollment->batch_id, 'month' => $month, 'student_search' => $enrollment->student?->student_code]) }}" class="btn btn-sm btn-outline-success">Collect</a>
                                        @else
                                            <a href="{{ route('admin.payments.index', ['search' => $enrollment->student?->student_code]) }}" class="btn btn-sm btn-outline-secondary">History</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No due records found for this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($dueEnrollments->hasPages())
                <div class="mt-4">{{ $dueEnrollments->links() }}</div>
            @endif
        </div>
    </div>
@endsection
