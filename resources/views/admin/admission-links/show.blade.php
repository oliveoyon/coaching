@extends('layouts.admin')

@section('title', 'Admission Link Details')
@section('page-title', 'Admission Link Details')
@section('page-subtitle', 'Share this link in the batch WhatsApp group and monitor incoming requests.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card page-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Link Information</h2>

                    <div class="mb-3">
                        <div class="text-muted small">Batch</div>
                        <div class="fw-semibold">{{ $admissionLink->batch?->name }}</div>
                        <div class="small text-muted">
                            {{ $admissionLink->batch?->academicClass?->name }}
                            @if ($admissionLink->batch?->subject)
                                | {{ $admissionLink->batch?->subject?->name }}
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Public Link</div>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ route('admission.apply', $admissionLink->token) }}" readonly>
                            <a href="{{ route('admission.apply', $admissionLink->token) }}" target="_blank" class="btn btn-outline-primary">Open</a>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Status</div>
                            <span class="badge rounded-pill {{ $admissionLink->isOpen() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $admissionLink->isOpen() ? 'Open' : 'Closed' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Expires</div>
                            <div class="fw-semibold">{{ $admissionLink->expires_at?->format('d M Y h:i A') ?: 'No expiry' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Recent Requests</h2>
                        <a href="{{ route('admin.admission-requests.index') }}" class="btn btn-sm btn-outline-secondary">All Requests</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Guardian WhatsApp</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admissionLink->admissionRequests as $request)
                                    <tr>
                                        <td>{{ $request->name }}</td>
                                        <td>{{ $request->guardian_phone }}</td>
                                        <td>
                                            <span class="badge rounded-pill {{ $request->status === 'pending' ? 'text-bg-warning' : ($request->status === 'approved' ? 'text-bg-success' : 'text-bg-secondary') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.admission-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No requests submitted yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
