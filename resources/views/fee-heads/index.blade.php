@extends('layouts.app')

@section('title', 'Fee Heads')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Fee Foundation</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Fee Heads</h1>
            <p class="text-secondary mb-0">Define reusable fee categories like admission, tuition, exam, or custom charges.</p>
        </div>
        @can('create', \App\Models\FeeHead::class)
            <a href="{{ route('fee-heads.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Add Fee Head</a>
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
                            <th>Fee Head</th>
                            <th>Type</th>
                            <th>Frequency</th>
                            <th>Structures</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($feeHeads as $feeHead)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $feeHead->name }}</div>
                                    <div class="small text-secondary">{{ $feeHead->code }}</div>
                                </td>
                                <td>{{ str($feeHead->type)->replace('_', ' ')->title() }}</td>
                                <td>{{ str($feeHead->frequency)->replace('_', ' ')->title() }}</td>
                                <td>{{ $feeHead->structures_count }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $feeHead->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $feeHead->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('fee-heads.edit', $feeHead) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                        @can('delete', $feeHead)
                                            <form method="POST" action="{{ route('fee-heads.destroy', $feeHead) }}" onsubmit="return confirm('Delete this fee head?');">
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
                                <td colspan="6" class="text-center py-5 text-secondary">No fee heads are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $feeHeads->links() }}</div>
    </div>
@endsection
