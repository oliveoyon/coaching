@extends('layouts.admin')

@section('title', 'Due List')
@section('page-title', 'Due List By Month')
@section('page-subtitle', 'Find dues only when needed.')

@section('content')
    <style>
        .due-list-page .filter-shell {
            border-radius: 1rem;
        }

        .due-list-page .table td,
        .due-list-page .table th {
            vertical-align: middle;
        }
    </style>

    <div class="due-list-page">
        <div class="card page-card filter-shell mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <form method="GET" action="{{ route('admin.payments.due-list') }}" class="row g-2 w-100">
                        <div class="col-12 col-lg-4">
                            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Student, code, phone, guardian, or batch">
                        </div>
                        <div class="col-6 col-lg-3">
                            <select name="batch_id" class="form-select">
                                <option value="">All batches</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected($batchId === $batch->id)>{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <input type="month" name="month" value="{{ $month }}" class="form-control">
                        </div>
                        <div class="col-3 col-lg-auto">
                            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                        </div>
                        <div class="col-3 col-lg-auto">
                            <a href="{{ route('admin.payments.due-list') }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>

                    <a href="{{ route('admin.payments.index', ['tab' => 'pending']) }}" class="btn btn-outline-secondary">Pending MFS</a>
                </div>
            </div>
        </div>

        <div class="card page-card">
            <div class="card-body p-4">
                @if ($hasFilters)
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
                                        @php($summary = app(\App\Http\Controllers\Admin\PaymentController::class)->feeSummary($enrollment, $batchFee, $resolvedMonth))
                                        @continue($summary['remaining'] <= 0)
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
                                                    @if ($isMonthly && $resolvedMonth)
                                                        | {{ $resolvedMonth }}
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
                                                    <a href="{{ route('admin.payments.create', ['student' => $enrollment->student_id, 'batch' => $enrollment->batch_id, 'month' => $resolvedMonth, 'student_search' => $enrollment->student?->student_code]) }}" class="btn btn-sm btn-outline-success">Collect</a>
                                                @else
                                                    <a href="{{ route('admin.payments.index', ['search' => $enrollment->student?->student_code, 'tab' => 'history']) }}" class="btn btn-sm btn-outline-secondary">History</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No due records found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($dueEnrollments->hasPages())
                        <div class="mt-4">{{ $dueEnrollments->links() }}</div>
                    @endif
                @else
                    <div class="py-5 text-center text-muted">
                        Use the filters above to open the due list.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
