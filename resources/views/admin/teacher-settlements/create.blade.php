@extends('layouts.admin')

@section('title', 'Record Teacher Settlement')
@section('page-title', 'Record Teacher Settlement')
@section('page-subtitle', 'Use this when admin or accounts pays a teacher against outstanding liabilities.')

@section('content')
    <style>
        .teacher-settlement-form .section-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .teacher-settlement-form .section-head {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1rem;
        }
    </style>

    <div class="row justify-content-center teacher-settlement-form">
        <div class="col-xl-9 col-xxl-8">
            <div class="card page-card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 p-lg-5" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 42%, #f8fafc 100%);">
                        <form method="POST" action="{{ route('admin.teacher-settlements.store') }}">
                            @csrf

                            <div class="section-card p-4 p-lg-4">
                                <div class="section-head">Settlement Details</div>
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
                                        <textarea name="note" id="note" rows="4" class="form-control @error('note') is-invalid @enderror" placeholder="Cash paid, bank transfer, partial settlement, or reference note">{{ old('note') }}</textarea>
                                        @error('note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.teacher-settlements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Settlement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
