@extends('layouts.app')

@section('title', 'Batches')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Batches</h1>
            <p class="text-secondary mb-0">
                {{ auth()->user()->isAdmin() ? 'Manage all academic batches in your coaching center.' : 'Manage only the batches you own as teacher-owner.' }}
            </p>
        </div>
        @can('create', \App\Models\Batch::class)
            <a href="{{ route('batches.create') }}" class="btn btn-primary rounded-4 px-4 fw-semibold">
                <i class="bi bi-plus-circle me-2"></i>Add Batch
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card">
            <div class="table-responsive">
                <table class="table module-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Batch</th>
                            <th>Program</th>
                            <th>Subject</th>
                            <th>Teacher Owner</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $batch->name }}</div>
                                    <div class="small text-secondary">{{ $batch->code }}</div>
                                </td>
                                <td>{{ $batch->program?->name ?? 'Not set' }}</td>
                                <td>{{ $batch->subject?->name ?? 'Not set' }}</td>
                                <td>{{ $batch->ownerTeacher?->name ?? 'Not set' }}</td>
                                <td>
                                    @if ($batch->schedules->isEmpty())
                                        <span class="text-secondary">No routine yet</span>
                                    @else
                                        <div class="small">
                                            @foreach ($batch->schedules as $schedule)
                                                <div>{{ ucfirst($schedule->day_of_week) }} • {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="soft-badge {{ $batch->status === \App\Models\Batch::STATUS_ACTIVE ? 'soft-success' : ($batch->status === \App\Models\Batch::STATUS_COMPLETED ? 'soft-primary' : 'soft-warning') }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-outline-primary rounded-4 px-3">Manage</a>
                                        @can('delete', $batch)
                                            <form method="POST" action="{{ route('batches.destroy', $batch) }}" onsubmit="return confirm('Delete this batch?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-4 px-3">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">No batches are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $batches->links() }}
        </div>
    </div>
@endsection
