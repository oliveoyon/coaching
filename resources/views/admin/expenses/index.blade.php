@extends('layouts.admin')

@section('title', 'Expenses')
@section('page-title', 'Expense Management')
@section('page-subtitle', 'Track common and teacher-specific expenses with monthly finance visibility.')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">{{ $month }} Total Expense</div>
                    <div class="h4 mb-0">{{ number_format($monthlySummary['total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">{{ $month }} Common Expense</div>
                    <div class="h4 mb-0">{{ number_format($monthlySummary['common'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">{{ $month }} Teacher Expense</div>
                    <div class="h4 mb-0">{{ number_format($monthlySummary['teacher'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-2 w-100">
                    <div class="col-12 col-xl-4">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by note or teacher">
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
                    <i class="bi bi-plus-circle me-1"></i> Add Expense
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Teacher</th>
                            <th>Amount</th>
                            <th>Note</th>
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
                                <td>{{ $expense->teacher?->user?->name ?: '-' }}</td>
                                <td class="fw-semibold">{{ number_format((float) $expense->amount, 2) }}</td>
                                <td>{{ $expense->note ?: '-' }}</td>
                                <td>{{ $expense->creator?->name ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" class="d-inline" onsubmit="return confirm('Delete this expense entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No expenses found.</td>
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
@endsection
