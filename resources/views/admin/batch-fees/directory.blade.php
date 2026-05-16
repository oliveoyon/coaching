@extends('layouts.admin')

@section('title', 'Batch Fee Setup')
@section('page-title', 'Batch Fee Setup')
@section('page-subtitle', 'Select a batch')

@section('content')
    <style>
        .batch-fee-directory .fee-setup-state {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1;
        }

        .batch-fee-directory .fee-setup-state.is-set {
            background: #ecfdf5;
            color: #047857;
        }

        .batch-fee-directory .fee-setup-state.is-missing {
            background: #fef2f2;
            color: #b91c1c;
        }
    </style>

    <div class="card page-card batch-fee-directory">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Schedule</th>
                            <th>Teachers</th>
                            <th>Fee Setup</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $batch->name }}</div>
                                    <div class="small text-muted">
                                        {{ $batch->academicClass?->name }}
                                        @if ($batch->subject)
                                            | {{ $batch->subject->name }}
                                        @endif
                                    </div>
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
                                    @endphp

                                    @forelse ($schedulePreview as $preview)
                                        <div class="small">{{ $preview }}</div>
                                    @empty
                                        <span class="text-muted small">Not set</span>
                                    @endforelse
                                </td>
                                <td>
                                    @forelse ($batch->teachers as $teacher)
                                        <span class="badge rounded-pill text-bg-light border me-1 mb-1">{{ $teacher->user?->name }}</span>
                                    @empty
                                        <span class="text-muted small">No teacher</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($batch->batchFees->count() > 0)
                                        <div class="fw-semibold">{{ $batch->batchFees->count() }} fee {{ $batch->batchFees->count() === 1 ? 'item' : 'items' }}</div>
                                        <div class="small mt-1">
                                            <span class="fee-setup-state is-set">Fees configured</span>
                                        </div>
                                    @else
                                        <div class="fw-semibold text-danger-emphasis">Not yet set</div>
                                        <div class="small mt-1">
                                            <span class="fee-setup-state is-missing">Needs fee setup</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $batch->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.batch-fees.index', $batch) }}" class="btn btn-sm btn-outline-primary">
                                        {{ $batch->batchFees->count() > 0 ? 'Manage' : 'Set Fees' }}
                                    </a>
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
                <div class="mt-4">{{ $batches->links() }}</div>
            @endif
        </div>
    </div>
@endsection
