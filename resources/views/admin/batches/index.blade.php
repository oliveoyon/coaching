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
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-3">
                    <div class="text-success-emphasis small fw-semibold mb-1">Active</div>
                    <div class="fs-4 fw-bold text-success">{{ $activeCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #f5f3ff 0%, #e9d5ff 100%);">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold mb-1">Inactive</div>
                    <div class="fs-4 fw-bold text-secondary">{{ $inactiveCount }}</div>
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
                                    @can('manage fee setup')
                                        <a href="{{ route('admin.batch-fees.index', $batch) }}" class="btn btn-sm border-0 text-primary-emphasis" style="background: #dbeafe;">Fees</a>
                                    @endcan
                                    <a href="{{ route('admin.batches.show', $batch) }}" class="btn btn-sm border-0 text-secondary-emphasis" style="background: #e5e7eb;">View</a>
                                    @can('manage batches')
                                        <a href="{{ route('admin.batches.edit', $batch) }}" class="btn btn-sm border-0 text-success-emphasis" style="background: #dcfce7;">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No batches found.</td>
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
