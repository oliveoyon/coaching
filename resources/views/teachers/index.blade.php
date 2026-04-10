@extends('layouts.app')

@section('title', 'Teachers')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Teacher Module</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Teachers</h1>
            <p class="text-secondary mb-0">
                {{ auth()->user()->isAdmin() ? 'Manage all teacher profiles inside your coaching center.' : 'Review and maintain your own teacher profile.' }}
            </p>
        </div>
        @can('create', \App\Models\Teacher::class)
            <a href="{{ route('teachers.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Add Teacher</a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Linked User</th>
                            <th>Status</th>
                            <th>Specializations</th>
                            <th>Batch Owner</th>
                            <th>Fee Collector</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teachers as $teacher)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $teacher->name }}</div>
                                    <div class="small text-secondary">{{ $teacher->email ?: 'No email added yet' }}</div>
                                </td>
                                <td>{{ $teacher->user?->email ?? 'Not linked' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $teacher->status === \App\Models\Teacher::STATUS_ACTIVE ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($teacher->status) }}
                                    </span>
                                </td>
                                <td>{{ collect($teacher->subject_specializations)->join(', ') ?: 'Not set' }}</td>
                                <td>{{ $teacher->can_own_batches ? 'Enabled' : 'Disabled' }}</td>
                                <td>{{ $teacher->can_collect_fees ? 'Enabled' : 'Disabled' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                        @can('delete', $teacher)
                                            <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" onsubmit="return confirm('Delete this teacher profile?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">No teacher profiles are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $teachers->links() }}
        </div>
    </div>
@endsection
