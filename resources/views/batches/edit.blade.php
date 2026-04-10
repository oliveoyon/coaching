@extends('layouts.app')

@section('title', 'Edit Batch')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $batch->name }}</h1>
            <p class="text-secondary mb-0">Update batch ownership, academic mapping, and schedule details.</p>
        </div>
        <span class="soft-badge {{ $batch->status === \App\Models\Batch::STATUS_ACTIVE ? 'soft-success' : ($batch->status === \App\Models\Batch::STATUS_COMPLETED ? 'soft-primary' : 'soft-warning') }}">
            {{ ucfirst($batch->status) }}
        </span>
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('batches.update', $batch) }}">
            @csrf
            @method('PATCH')
            @include('batches.partials.form', ['submitLabel' => 'Update Batch'])
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('addScheduleRow')?.addEventListener('click', function () {
            const template = document.getElementById('scheduleRowTemplate');
            const container = document.getElementById('scheduleRows');

            if (template && container) {
                container.insertAdjacentHTML('beforeend', template.innerHTML);
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-schedule-row')) {
                const rows = document.querySelectorAll('#scheduleRows .schedule-row');

                if (rows.length > 1) {
                    event.target.closest('.schedule-row')?.remove();
                }
            }
        });
    </script>
@endpush
