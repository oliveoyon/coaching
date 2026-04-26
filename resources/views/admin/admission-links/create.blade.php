@extends('layouts.admin')

@section('title', 'Create Admission Link')
@section('page-title', 'Create Admission Link')
@section('page-subtitle', 'This link will be shared in a specific batch WhatsApp group for self-submitted student admission requests.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.admission-links.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="batch_id" class="form-label">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror" required>
                            <option value="">Select batch</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}" @selected((string) old('batch_id', $selectedBatchId ?? '') === (string) $batch->id)>
                                    {{ $batch->name }} ({{ $batch->academicClass?->name }}@if($batch->subject) - {{ $batch->subject?->name }}@endif)
                                </option>
                            @endforeach
                        </select>
                        @error('batch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="title" class="form-label">Link Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Optional">
                        <div class="form-text">Example: April admission, waiting list, or re-admission.</div>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="expires_at" class="form-label">Expiry Date & Time</label>
                        <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" class="form-control @error('expires_at') is-invalid @enderror">
                        <div class="form-text">Optional. Leave blank to keep the link open.</div>
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.admission-links.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Link</button>
                </div>
            </form>
        </div>
    </div>
@endsection
