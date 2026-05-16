@extends('layouts.admin')

@section('title', 'Admission Requests')
@section('page-title', 'Admission Requests')
@section('page-subtitle', 'Review student admission requests.')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-primary mb-2">Total Requests</div>
                    <div class="h4 mb-0 text-dark">{{ $totalRequests }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-warning mb-2">Pending Requests</div>
                    <div class="h4 mb-0 text-dark">{{ $pendingRequests }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-success mb-2">Approved Requests</div>
                    <div class="h4 mb-0 text-dark">{{ $approvedRequests }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <a href="{{ route('admin.admission-links.index') }}" class="btn btn-outline-secondary">Back to Links</a>
                <form method="GET" action="{{ route('admin.admission-requests.index') }}" class="row g-3 align-items-end flex-grow-1 justify-content-end">
                    <div class="col-lg-4">
                        <label for="search" class="form-label">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Student, phone, guardian, or batch"
                        >
                    </div>
                    <div class="col-lg-3">
                        <label for="batch_id" class="form-label">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-select">
                            <option value="">All batches</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}" @selected($batchId === $batch->id)>{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="approved" @selected($status === 'approved')>Approved</option>
                            <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </form>
            </div>

            @if ($hasFilters)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Batch</th>
                                <th>Guardian</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr>
                                    <td>{{ $request->name }}</td>
                                    <td>{{ $request->batch?->name }}</td>
                                    <td>{{ $request->guardian_phone }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $request->status === 'pending' ? 'text-bg-warning' : ($request->status === 'approved' ? 'text-bg-success' : 'text-bg-secondary') }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->created_at?->format('d M Y h:i A') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.admission-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No admission requests found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($requests->hasPages())
                    <div class="mt-4">{{ $requests->links() }}</div>
                @endif
            @else
                <div class="py-5 text-center text-muted">
                    Use the filters above to find admission requests.
                </div>
            @endif
        </div>
    </div>
@endsection
