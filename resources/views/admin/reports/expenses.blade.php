@extends('layouts.admin')

@section('title', 'Expense Reports')
@section('page-title', 'Expense Reports')
@section('page-subtitle', 'Monthly expense summary with detailed common and teacher expense entries.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.expenses') }}" class="row g-3 align-items-end">
                <div class="col-lg-2">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                </div>
                <div class="col-lg-2">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="common" @selected($type === 'common')>Common</option>
                        <option value="teacher" @selected($type === 'teacher')>Teacher</option>
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
                <div class="col-lg-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Expense note or teacher name">
                </div>
                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @forelse ($summary as $row)
            <div class="col-md-4">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">{{ ucfirst($row->expense_type) }} Expense</div>
                        <div class="fs-4 fw-semibold mt-2">{{ number_format((float) $row->total_amount, 2) }}</div>
                        <div class="small text-muted mt-1">{{ $row->entry_count }} entries</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border mb-0">No expense summary found for the selected filters.</div>
            </div>
        @endforelse
    </div>

    <div class="card page-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Teacher</th>
                            <th>Note</th>
                            <th>Created By</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date?->format('d M Y') }}</td>
                                <td>{{ ucfirst($expense->type) }}</td>
                                <td>{{ $expense->teacher?->user?->name ?: '-' }}</td>
                                <td>{{ $expense->note }}</td>
                                <td>{{ $expense->creator?->name ?: '-' }}</td>
                                <td class="text-end">{{ number_format((float) $expense->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No expense rows found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($expenses->hasPages())
            <div class="card-footer bg-white">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
@endsection
