@extends('layouts.app')

@section('title', 'Fee Structures')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Fee Foundation</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Fee Structures</h1>
            <p class="text-secondary mb-0">Define actual charge amounts and where those charges apply.</p>
        </div>
        @can('create', \App\Models\FeeStructure::class)
            <a href="{{ route('fee-structures.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Add Fee Structure</a>
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
                            <th>Structure</th>
                            <th>Fee Head</th>
                            <th>Billing Model</th>
                            <th>Applies To</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($structures as $structure)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $structure->title }}</div>
                                    <div class="small text-secondary">{{ $structure->notes ?: 'No notes added' }}</div>
                                </td>
                                <td>{{ $structure->feeHead?->name ?? 'Not set' }}</td>
                                <td>{{ $structure->billing_model ? str($structure->billing_model)->replace('_', ' ')->title() : 'All Models' }}</td>
                                <td>{{ str($structure->applicable_type)->replace('_', ' ')->title() }}{{ $structure->applicable_id ? ' #'.$structure->applicable_id : '' }}</td>
                                <td>{{ number_format((float) $structure->amount, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $structure->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $structure->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('fee-structures.edit', $structure) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                        @can('delete', $structure)
                                            <form method="POST" action="{{ route('fee-structures.destroy', $structure) }}" onsubmit="return confirm('Delete this fee structure?');">
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
                                <td colspan="7" class="text-center py-5 text-secondary">No fee structures are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $structures->links() }}</div>
    </div>
@endsection
