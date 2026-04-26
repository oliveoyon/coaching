@extends('layouts.admin')

@section('title', 'Withdraw Enrollment')
@section('page-title', 'Withdraw Enrollment')
@section('page-subtitle', 'Ending an enrollment keeps history intact and will help stop future billing for this batch later.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="alert alert-warning">
                <div class="fw-semibold">{{ $enrollment->student?->name }}</div>
                <div class="small mb-0">Batch: {{ $enrollment->batch?->name }}</div>
            </div>

            <form method="POST" action="{{ route('admin.enrollments.withdraw', $enrollment) }}">
                @csrf
                @method('PATCH')

                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Withdrawal Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->format('Y-m-d')) }}" class="form-control @error('end_date') is-invalid @enderror" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Confirm Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
@endsection
