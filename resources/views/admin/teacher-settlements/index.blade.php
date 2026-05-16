@extends('layouts.admin')

@section('title', 'Teacher Settlements')
@section('page-title', request()->routeIs('teacher.settlements.*') ? 'My Settlements' : 'Teacher Settlements')
@section('page-subtitle', 'Track teacher payables, settlement history, and who collected the source payments.')

@section('content')
    <style>
        .teacher-settlement-page .summary-table td,
        .teacher-settlement-page .summary-table th,
        .teacher-settlement-page .history-table td,
        .teacher-settlement-page .history-table th {
            vertical-align: middle;
            font-size: 0.92rem;
        }

        .teacher-settlement-page .summary-table tbody tr,
        .teacher-settlement-page .history-table tbody tr {
            border-color: rgba(15, 23, 42, 0.06);
        }

        .teacher-settlement-page .history-table .amount-strong,
        .teacher-settlement-page .summary-table .amount-strong {
            font-weight: 700;
            color: #0f172a;
        }
    </style>

    <div class="teacher-settlement-page">
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <form method="GET" action="{{ route($routeName) }}" class="row g-2 w-100">
                    <div class="col-12 col-xl-4">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Teacher name">
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <input type="month" name="month" value="{{ $month }}" class="form-control">
                    </div>
                    @if (!request()->routeIs('teacher.settlements.*'))
                        <div class="col-6 col-md-3 col-xl-3">
                            <select name="teacher_id" class="form-select">
                                <option value="">All Teachers</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected((string) $teacherId === (string) $teacher->id)>{{ $teacher->user?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-3 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-3 col-md-auto">
                        <a href="{{ route($routeName) }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                @can('settle teacher payments')
                    <a href="{{ route('admin.teacher-settlements.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Record Settlement
                    </a>
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 summary-table">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher</th>
                            <th>Earnings</th>
                            <th>Settlements</th>
                            <th>Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summaryRows as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row['teacher']->user?->name }}</div>
                                </td>
                                <td>
                                    <div class="amount-strong">{{ number_format($row['earned'], 2) }}</div>
                                    <div class="small text-muted">{{ $month }}: {{ number_format($row['earned_this_month'], 2) }}</div>
                                </td>
                                <td>
                                    <div class="amount-strong">{{ number_format($row['settled'], 2) }}</div>
                                    <div class="small text-muted">{{ $month }}: {{ number_format($row['settled_this_month'], 2) }}</div>
                                </td>
                                <td class="amount-strong">{{ number_format($row['outstanding'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No payable summary found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Settlement History</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 history-table">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Teacher</th>
                            <th>Amount / Allocations</th>
                            <th>Paid By</th>
                            <th>Source Collectors</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settlements as $settlement)
                            <tr>
                                <td>{{ $settlement->settlement_date?->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $settlement->teacher?->user?->name ?: '-' }}</div>
                                </td>
                                <td>
                                    <div class="amount-strong">{{ number_format((float) $settlement->amount, 2) }}</div>
                                    <div class="small text-muted">{{ $settlement->items->count() }} entr{{ $settlement->items->count() === 1 ? 'y' : 'ies' }}</div>
                                </td>
                                <td>{{ $settlement->payer?->name ?: '-' }}</td>
                                <td class="small text-muted">{{ $settlement->items->pluck('distribution.payment.collector.name')->filter()->unique()->implode(', ') ?: '-' }}</td>
                                <td class="small text-muted">{{ $settlement->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No settlement records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($settlements->hasPages())
                <div class="mt-4">{{ $settlements->links() }}</div>
            @endif
        </div>
    </div>
    </div>
@endsection
