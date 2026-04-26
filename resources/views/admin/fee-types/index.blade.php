@extends('layouts.admin')

@section('title', 'Fee Types')
@section('page-title', 'Fee Types')
@section('page-subtitle', 'Define reusable fee heads like tuition, admission, or exam fee with their collection frequency.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.fee-types.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search fee type">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.fee-types.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.fee-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Fee Type
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Batches</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($feeTypes as $feeType)
                            <tr>
                                <td class="fw-semibold">{{ $feeType->name }}</td>
                                <td>{{ $feeType->code }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $feeType->frequency)) }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $feeType->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($feeType->status) }}
                                    </span>
                                </td>
                                <td>{{ $feeType->batch_fees_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.fee-types.edit', $feeType) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No fee types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
