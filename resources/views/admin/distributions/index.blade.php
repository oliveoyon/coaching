@extends('layouts.admin')

@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('page-subtitle', 'Approved payment shares are recorded here. Teachers only see their own earnings.')

@section('content')
    <style>
        .distribution-page .summary-card {
            border: 0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            border-radius: 1rem;
            height: 100%;
        }

        .distribution-page .summary-card .card-body {
            padding: 0.95rem 1rem;
        }

        .distribution-page .summary-card .label {
            font-size: 0.76rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .distribution-page .summary-card .value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .distribution-page .table td,
        .distribution-page .table th {
            vertical-align: middle;
            font-size: 0.92rem;
        }
    </style>

    <div class="distribution-page">
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl">
            <div class="card summary-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body">
                    <div class="label text-primary">Total Earnings</div>
                    <div class="value">{{ number_format($summary['overall'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="card summary-card" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body">
                    <div class="label text-success">{{ $currentMonth }} Earnings</div>
                    <div class="value">{{ number_format($summary['month'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="card summary-card" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
                <div class="card-body">
                    <div class="label" style="color:#7c3aed;">Settled</div>
                    <div class="value">{{ number_format($summary['settled'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="card summary-card" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                <div class="card-body">
                    <div class="label text-warning">Outstanding</div>
                    <div class="value">{{ number_format($summary['outstanding'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl">
            <div class="card summary-card" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);">
                <div class="card-body">
                    <div class="label text-secondary">Entries</div>
                    <div class="value">{{ $summary['count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <form method="GET" action="{{ route($routeName) }}" class="row g-2 w-100">
                    <div class="col-12 col-xl-5">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Teacher, student, code, phone, or batch">
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <input type="month" name="month" value="{{ $displayMonth }}" class="form-control">
                    </div>
                    <div class="col-3 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-3 col-md-auto">
                        <a href="{{ route($routeName) }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>

            @if ($hasFilters)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Teacher</th>
                                <th>Student / Batch</th>
                                <th>Fee</th>
                                <th>Collected By</th>
                                <th>Payment / Share</th>
                                <th>Settled / Due</th>
                                <th>Approved Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($distributions as $distribution)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $distribution->teacher?->user?->name ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.student-profiles.show', $distribution->payment->enrollment->student) }}" class="fw-semibold text-decoration-none">
                                            {{ $distribution->payment->enrollment->student?->name }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $distribution->payment->enrollment->student?->student_code }}
                                            | {{ $distribution->payment->enrollment->batch?->name }}
                                            @if ($distribution->payment->enrollment->batch?->subject)
                                                | {{ $distribution->payment->enrollment->batch?->subject?->name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $distribution->payment->batchFee?->feeType?->name ?: '-' }}</div>
                                        <div class="small text-muted">{{ $distribution->payment->month ?: '-' }}</div>
                                    </td>
                                    <td>{{ $distribution->payment->collector?->name ?: '-' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ number_format((float) $distribution->payment->amount, 2) }}</div>
                                        <div class="small text-muted">Share {{ number_format((float) $distribution->amount, 2) }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ number_format((float) $distribution->settlementItems->sum('amount'), 2) }}</div>
                                        <div class="small text-muted">Due {{ number_format(max(0, (float) $distribution->amount - (float) $distribution->settlementItems->sum('amount')), 2) }}</div>
                                    </td>
                                    <td>{{ $distribution->payment->payment_date?->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No distribution records found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($distributions->hasPages())
                    <div class="mt-4">{{ $distributions->links() }}</div>
                @endif
            @else
                <div class="py-5 text-center text-muted">
                    Use the filters above to open earnings records.
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection
