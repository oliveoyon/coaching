@extends('layouts.admin')

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('page-subtitle', 'Approved payment shares are recorded here. Teachers only see their own earnings.')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Total Earnings</div>
                    <div class="h4 mb-0">{{ number_format($summary['overall'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">{{ $currentMonth }} Earnings</div>
                    <div class="h4 mb-0">{{ number_format($summary['month'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Settled</div>
                    <div class="h4 mb-0">{{ number_format($summary['settled'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Outstanding</div>
                    <div class="h4 mb-0">{{ number_format($summary['outstanding'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Distribution Entries</div>
                    <div class="h4 mb-0">{{ $summary['count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <form method="GET" action="{{ route($routeName) }}" class="row g-2 w-100">
                    <div class="col-12 col-xl-5">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by teacher, student, student code, phone, or batch">
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <input type="month" name="month" value="{{ $month }}" class="form-control">
                    </div>
                    <div class="col-3 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-3 col-md-auto">
                        <a href="{{ route($routeName) }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher</th>
                            <th>Student</th>
                            <th>Batch</th>
                            <th>Fee Item</th>
                            <th>Month</th>
                            <th>Collected By</th>
                            <th>Payment Amount</th>
                            <th>Share</th>
                            <th>Settled</th>
                            <th>Outstanding</th>
                            <th>Approved Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($distributions as $distribution)
                            <tr>
                                <td>{{ $distribution->teacher?->user?->name ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.student-profiles.show', $distribution->payment->enrollment->student) }}" class="fw-semibold text-decoration-none">
                                        {{ $distribution->payment->enrollment->student?->name }}
                                    </a>
                                    <div class="small text-muted">
                                        {{ $distribution->payment->enrollment->student?->student_code }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $distribution->payment->enrollment->batch?->name }}</div>
                                    <div class="small text-muted">
                                        {{ $distribution->payment->enrollment->batch?->academicClass?->name }}
                                        @if ($distribution->payment->enrollment->batch?->subject)
                                            | {{ $distribution->payment->enrollment->batch?->subject?->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $distribution->payment->batchFee?->feeType?->name ?: '-' }}</td>
                                <td>{{ $distribution->payment->month ?: '-' }}</td>
                                <td>{{ $distribution->payment->collector?->name ?: '-' }}</td>
                                <td>{{ number_format((float) $distribution->payment->amount, 2) }}</td>
                                <td class="fw-semibold">{{ number_format((float) $distribution->amount, 2) }}</td>
                                <td>{{ number_format((float) $distribution->settlementItems->sum('amount'), 2) }}</td>
                                <td class="fw-semibold">{{ number_format(max(0, (float) $distribution->amount - (float) $distribution->settlementItems->sum('amount')), 2) }}</td>
                                <td>{{ $distribution->payment->payment_date?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">No distribution records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($distributions->hasPages())
                <div class="mt-4">{{ $distributions->links() }}</div>
            @endif
        </div>
    </div>
@endsection
