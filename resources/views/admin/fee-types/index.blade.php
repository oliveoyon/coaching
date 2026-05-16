@extends('layouts.admin')

@section('title', 'Fee Types')
@section('page-title', 'Fee Types')
@section('page-subtitle', 'Manage fee names and type')

@section('content')
    @php
        $monthlyCount = $feeTypes->getCollection()->where('frequency', 'monthly')->count();
        $oneTimeCount = $feeTypes->getCollection()->where('frequency', 'one_time')->count();
        $manualCount = $feeTypes->getCollection()->where('frequency', 'manual')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-primary mb-2">Monthly</div>
                    <div class="fs-3 fw-semibold text-dark">{{ $monthlyCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-success mb-2">One Time</div>
                    <div class="fs-3 fw-semibold text-dark">{{ $oneTimeCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-warning mb-2">Manual</div>
                    <div class="fs-3 fw-semibold text-dark">{{ $manualCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.fee-types.index') }}" class="row g-2 flex-grow-1">
                    <div class="col-12 col-lg-6">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search fee type">
                    </div>
                    <div class="col-6 col-lg-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-lg-auto">
                        <a href="{{ route('admin.fee-types.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.fee-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Fee Type
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
                                <td>
                                    <div class="fw-semibold">{{ $feeType->name }}</div>
                                </td>
                                <td>{{ $feeType->code }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $feeType->frequency === 'monthly' ? 'text-bg-primary' : ($feeType->frequency === 'one_time' ? 'text-bg-success' : 'text-bg-warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $feeType->frequency)) }}
                                    </span>
                                </td>
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
