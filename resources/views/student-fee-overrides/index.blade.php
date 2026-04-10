@extends('layouts.app')

@section('title', 'Fee Overrides')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Fee Foundation</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Student Fee Overrides</h1>
            <p class="text-secondary mb-0">Apply student-specific pricing exceptions without changing the base fee structure.</p>
        </div>
        @can('create', \App\Models\StudentFeeOverride::class)
            <a href="{{ route('student-fee-overrides.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Add Override</a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Structure</th>
                            <th>Amount</th>
                            <th>Validity</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($overrides as $override)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $override->student?->name }}</div>
                                    <div class="small text-secondary">{{ $override->student?->student_code }}</div>
                                </td>
                                <td>{{ $override->feeStructure?->title ?? 'Not set' }}</td>
                                <td>{{ number_format((float) $override->amount, 2) }}</td>
                                <td>{{ optional($override->starts_on)->format('Y-m-d') ?: 'Any time' }}{{ $override->ends_on ? ' to '.optional($override->ends_on)->format('Y-m-d') : '' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $override->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $override->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('student-fee-overrides.edit', $override) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                        @can('delete', $override)
                                            <form method="POST" action="{{ route('student-fee-overrides.destroy', $override) }}" onsubmit="return confirm('Delete this override?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">No student-specific overrides are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $overrides->links() }}</div>
    </div>
@endsection
