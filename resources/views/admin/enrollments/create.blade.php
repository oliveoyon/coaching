@extends('layouts.admin')

@section('title', 'Enroll Student')
@section('page-title', 'Enroll Student')
@section('page-subtitle', 'Create an active student-to-batch enrollment without changing admission history.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.enrollments.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="student_id" class="form-label">Student</label>
                        <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>
                                    {{ $student->student_code }} - {{ $student->name }} ({{ $student->academicClass?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="batch_id" class="form-label">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror" required>
                            <option value="">Select batch</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}" @selected((string) old('batch_id') === (string) $batch->id)>
                                    {{ $batch->name }} ({{ $batch->academicClass?->name }}@if($batch->subject) - {{ $batch->subject?->name }}@endif)
                                </option>
                            @endforeach
                        </select>
                        @error('batch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Enrollment</button>
                </div>
            </form>
        </div>
    </div>
@endsection
