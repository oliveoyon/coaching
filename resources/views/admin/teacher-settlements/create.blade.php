@extends('layouts.admin')

@section('title', 'Record Teacher Settlement')
@section('page-title', 'Record Teacher Settlement')
@section('page-subtitle', 'Use this when admin or accounts pays a teacher against outstanding liabilities.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.teacher-settlements.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                            <option value="">Select teacher</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((string) old('teacher_id') === (string) $teacher->id)>{{ $teacher->user?->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="amount" class="form-label">Settlement Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="settlement_date" class="form-label">Settlement Date</label>
                        <input type="date" name="settlement_date" id="settlement_date" value="{{ old('settlement_date', now()->format('Y-m-d')) }}" class="form-control @error('settlement_date') is-invalid @enderror" required>
                        @error('settlement_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" rows="4" class="form-control @error('note') is-invalid @enderror" placeholder="Cash paid, bank transfer, partial settlement, or any reference note">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.teacher-settlements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Settlement</button>
                </div>
            </form>
        </div>
    </div>
@endsection
