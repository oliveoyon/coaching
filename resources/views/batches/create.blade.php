@extends('layouts.app')

@section('title', 'Create Batch')

@section('page_header')
    <div>
        <h1 class="h3 fw-bold mb-1">Create Batch</h1>
        <p class="text-secondary mb-0">Set the academic context, assign the teacher-owner, and add routine-ready schedule rows.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('batches.store') }}">
            @csrf
            @include('batches.partials.form', ['submitLabel' => 'Save Batch'])
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
