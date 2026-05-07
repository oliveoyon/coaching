@extends('layouts.admin')

@section('title', 'Admission Requests')
@section('page-title', 'Admission Requests')
@section('page-subtitle', 'Review student admission requests.')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Total Requests</div>
                    <div class="h4 mb-0">{{ $requests->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Pending on This Page</div>
                    <div class="h4 mb-0">{{ $requests->getCollection()->where('status', 'pending')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Approved on This Page</div>
                    <div class="h4 mb-0">{{ $requests->getCollection()->where('status', 'approved')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('admin.admission-links.index') }}" class="btn btn-outline-secondary">Back to Links</a>
            </div>

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
                                <td colspan="6" class="text-center py-5 text-muted">No admission requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="mt-4">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection
