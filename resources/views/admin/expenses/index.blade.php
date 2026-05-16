@extends('layouts.admin')

@section('title', 'Expenses')
@section('page-title', 'Expense Management')
@section('page-subtitle', 'Track common and teacher expenses.')

@section('content')
    <style>
        .expense-page .summary-card {
            border: 0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            border-radius: 1rem;
            height: 100%;
        }

        .expense-page .summary-card .card-body {
            padding: 1rem 1.05rem;
        }

        .expense-page .summary-card .label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .expense-page .summary-card .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .expense-page .table td,
        .expense-page .table th {
            vertical-align: middle;
            font-size: 0.92rem;
        }
    </style>

    <div class="expense-page">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card summary-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body">
                    <div class="label text-primary">{{ $month }} Total</div>
                    <div class="value">{{ number_format($monthlySummary['total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);">
                <div class="card-body">
                    <div class="label text-secondary">{{ $month }} Common</div>
                    <div class="value">{{ number_format($monthlySummary['common'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                <div class="card-body">
                    <div class="label text-warning">{{ $month }} Teacher</div>
                    <div class="value">{{ number_format($monthlySummary['teacher'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-2 w-100">
                    <div class="col-12 col-xl-4">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Note or teacher">
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="common" @selected($type === 'common')>Common</option>
                            <option value="teacher" @selected($type === 'teacher')>Teacher</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <input type="month" name="month" value="{{ $month }}" class="form-control">
                    </div>
                    <div class="col-3 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-3 col-md-auto">
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Expense
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Teacher / Note</th>
                            <th>Amount</th>
                            <th>Created By</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date?->format('d M Y') }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $expense->type === 'common' ? 'text-bg-secondary' : 'text-bg-info' }}">
                                        {{ ucfirst($expense->type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $expense->teacher?->user?->name ?: '-' }}</div>
                                    <div class="small text-muted">{{ $expense->note ?: '-' }}</div>
                                </td>
                                <td class="fw-semibold">{{ number_format((float) $expense->amount, 2) }}</td>
                                <td>{{ $expense->creator?->name ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($expenses->hasPages())
                <div class="mt-4">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
    </div>
@endsection
