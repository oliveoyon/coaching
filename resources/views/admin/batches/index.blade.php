@extends('layouts.admin')

@section('title', 'Batch Management')
@section('page-title', 'Batch Management')
@section('page-subtitle', 'Manage batches')

@section('content')
    @php
        $activeCount = $batches->getCollection()->where('status', 'active')->count();
        $inactiveCount = $batches->getCollection()->where('status', 'inactive')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card page-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-1">This Page</div>
                    <div class="fs-3 fw-semibold">{{ $batches->count() }}</div>
                    <div class="small text-muted">Visible batches</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-1">Active</div>
                    <div class="fs-3 fw-semibold text-success">{{ $activeCount }}</div>
                    <div class="small text-muted">Running now</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-1">Inactive</div>
                    <div class="fs-3 fw-semibold text-secondary">{{ $inactiveCount }}</div>
                    <div class="small text-muted">Not running</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.batches.index') }}" class="row g-2 flex-grow-1">
                    <div class="col-12 col-lg-6">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search batch, class, subject, teacher">
                    </div>
                    <div class="col-6 col-lg-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-lg-auto">
                        <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                @can('manage batches')
                    <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Batch
                    </a>
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Fee</th>
                            <th>Teachers</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $batch->name }}</div>
                                    <div class="small text-muted">{{ $batch->academicClass?->name }}</div>
                                </td>
                                <td>
                                    <div>{{ $batch->subject?->name ?? 'General' }}</div>
                                    <div class="small text-muted">{{ ucfirst($batch->distribution_type) }}</div>
                                </td>
                                <td>
                                    @php
                                        $schedulePreview = collect($batch->schedule_entries)->take(2)->map(function ($entry) {
                                            $dayLabel = match ($entry['day']) {
                                                'sat' => 'Sat',
                                                'sun' => 'Sun',
                                                'mon' => 'Mon',
                                                'tue' => 'Tue',
                                                'wed' => 'Wed',
                                                'thu' => 'Thu',
                                                'fri' => 'Fri',
                                                default => ucfirst($entry['day']),
                                            };

                                            return $dayLabel.' '.$entry['start_time'].'-'.$entry['end_time'];
                                        });

                                        $hiddenScheduleCount = max(0, count($batch->schedule_entries) - 2);
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
                                    @if (count($batch->schedule_entries) > 0)
                                        @foreach ($schedulePreview as $preview)
                                            <div class="small">{{ $preview }}</div>
                                        @endforeach
                                        @if ($hiddenScheduleCount > 0)
                                            <div class="small text-muted">+{{ $hiddenScheduleCount }} more</div>
                                        @endif
                                    @elseif ($dayLabels || $batch->start_time || $batch->end_time)
                                        <div>{{ $dayLabels ?: 'Days not set' }}</div>
                                        @if ($batch->start_time || $batch->end_time)
                                            <div class="small text-muted">{{ $batch->start_time?->format('h:i A') ?: '-' }} - {{ $batch->end_time?->format('h:i A') ?: '-' }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>{{ number_format((float) $batch->monthly_fee, 2) }}</td>
                                <td>
                                    @foreach ($batch->teachers as $teacher)
                                        <span class="badge rounded-pill text-bg-primary-subtle text-primary border border-primary-subtle mb-1">{{ $teacher->user?->name }}</span>
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
                                <td colspan="7" class="text-center py-5 text-muted">No batches found.</td>
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
