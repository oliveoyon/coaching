@extends('layouts.admin')

@section('title', 'Public Admissions')
@section('page-title', 'Public Admission Links')
@section('page-subtitle', 'Create batch-specific admission links to share in WhatsApp groups and review incoming student requests.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <a href="{{ route('admin.admission-requests.index') }}" class="btn btn-outline-secondary">View Admission Requests</a>
                </div>
                <a href="{{ route('admin.admission-links.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Admission Link
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Link Title</th>
                            <th>Status</th>
                            <th>Pending Requests</th>
                            <th>Expires</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($links as $link)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $link->batch?->name }}</div>
                                    <div class="small text-muted">
                                        {{ $link->batch?->academicClass?->name }}
                                        @if ($link->batch?->subject)
                                            | {{ $link->batch?->subject?->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $link->title ?: 'General Admission Link' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $link->isOpen() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $link->isOpen() ? 'Open' : 'Closed' }}
                                    </span>
                                </td>
                                <td>{{ $link->pending_requests_count }}</td>
                                <td>{{ $link->expires_at?->format('d M Y h:i A') ?: 'No expiry' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.admission-links.show', $link) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No admission links created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($links->hasPages())
                <div class="mt-4">{{ $links->links() }}</div>
            @endif
        </div>
    </div>
@endsection
