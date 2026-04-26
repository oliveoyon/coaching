@extends('layouts.admin')

@section('title', 'Batch Management')
@section('page-title', 'Batch Management')
@section('page-subtitle', 'Create batches and assign one or multiple teachers based on distribution type.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.batches.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search batch, class, subject, or teacher">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <div>
                    @can('manage batches')
                        <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Batch
                        </a>
                    @endcan
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Schedule</th>
                            <th>Fee</th>
                            <th>Distribution</th>
                            <th>Teachers</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td class="fw-semibold">{{ $batch->name }}</td>
                                <td>{{ $batch->academicClass?->name }}</td>
                                <td>{{ $batch->subject?->name ?? 'General' }}</td>
                                <td>
                                    @php
                                        $dayLabels = collect($batch->schedule_days ?? [])->map(fn ($day) => match ($day) {
                                            'sat' => 'Sat',
                                            'sun' => 'Sun',
                                            'mon' => 'Mon',
                                            'tue' => 'Tue',
                                            'wed' => 'Wed',
                                            'thu' => 'Thu',
                                            'fri' => 'Fri',
                                            default => ucfirst($day),
                                        })->implode(', ');
                                    @endphp
                                    @if ($dayLabels || $batch->start_time || $batch->end_time)
                                        <div>{{ $dayLabels ?: '-' }}</div>
                                        <div class="small text-muted">
                                            {{ $batch->start_time?->format('h:i A') ?: '-' }} - {{ $batch->end_time?->format('h:i A') ?: '-' }}
                                        </div>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>{{ number_format((float) $batch->monthly_fee, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill text-bg-info">{{ ucfirst($batch->distribution_type) }}</span>
                                </td>
                                <td>
                                    @foreach ($batch->teachers as $teacher)
                                        <span class="badge rounded-pill text-bg-primary mb-1">{{ $teacher->user?->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $batch->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.batches.show', $batch) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @can('manage batches')
                                        <a href="{{ route('admin.batches.edit', $batch) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">No batches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($batches->hasPages())
                <div class="mt-4">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
