@extends('layouts.admin')

@section('title', 'Batch Details')
@section('page-title', 'Batch Details')
@section('page-subtitle', 'View batch information and assigned teachers.')

@section('content')
    @php
        $dayLabels = collect($batch->schedule_days ?? [])->map(fn ($day) => match ($day) {
            'sat' => 'Saturday',
            'sun' => 'Sunday',
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday',
            default => ucfirst($day),
        })->implode(', ');
    @endphp
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card page-card">
                <div class="card-body p-4">
                    @can('manage enrollments')
                        <div class="d-flex justify-content-end gap-2 mb-4">
                            <a href="{{ route('admin.batch-fees.index', $batch) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-receipt me-1"></i> Fee Setup
                            </a>
                            <a href="{{ route('admin.admission-links.create', ['batch_id' => $batch->id]) }}" class="btn btn-outline-primary">
                                <i class="bi bi-link-45deg me-1"></i> Create Admission Link
                            </a>
                        </div>
                    @endcan

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Batch Name</div>
                            <div class="fw-semibold">{{ $batch->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Class</div>
                            <div class="fw-semibold">{{ $batch->academicClass?->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Subject</div>
                            <div class="fw-semibold">{{ $batch->subject?->name ?? 'General Batch' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Monthly Fee</div>
                            <div class="fw-semibold">{{ number_format((float) $batch->monthly_fee, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Distribution Type</div>
                            <div><span class="badge rounded-pill text-bg-info">{{ ucfirst($batch->distribution_type) }}</span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Status</div>
                            <div>
                                <span class="badge rounded-pill {{ $batch->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Class Days</div>
                            <div class="fw-semibold">{{ $dayLabels ?: 'Not set' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Class Time</div>
                            <div class="fw-semibold">
                                @if ($batch->start_time || $batch->end_time)
                                    {{ $batch->start_time?->format('h:i A') ?: '-' }} - {{ $batch->end_time?->format('h:i A') ?: '-' }}
                                @else
                                    Not set
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card page-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Assigned Teachers</h2>
                    <div class="d-flex flex-column gap-2">
                        @forelse ($batch->teachers as $teacher)
                            <div class="border rounded-3 p-3">
                                <div class="fw-semibold">{{ $teacher->user?->name }}</div>
                                <div class="text-muted small">{{ $teacher->user?->email }}</div>
                            </div>
                        @empty
                            <div class="text-muted">No teachers assigned.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
