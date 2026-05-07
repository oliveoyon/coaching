@extends('layouts.admin')

@section('title', 'Open Attendance')
@section('page-title', 'Open Attendance')
@section('page-subtitle', 'Start attendance for one batch and one date.')

@section('content')
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card page-card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.attendance.store') }}" class="row g-4">
                        @csrf

                        <div class="col-md-7">
                            <label for="batch_id" class="form-label">Batch</label>
                            <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror" required>
                                <option value="">Select a batch</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(old('batch_id') == $batch->id)>
                                        {{ $batch->name }} | {{ $batch->academicClass?->name }}
                                        @if ($batch->subject)
                                            | {{ $batch->subject->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('batch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="attendance_date" class="form-label">Attendance Date</label>
                            <input type="date" name="attendance_date" id="attendance_date" value="{{ old('attendance_date', $defaultDate) }}" class="form-control @error('attendance_date') is-invalid @enderror" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Mode</label>
                            <div class="row g-3">
                                @foreach (['face' => 'Face', 'qr' => 'QR / Barcode', 'manual' => 'Manual'] as $value => $label)
                                    <div class="col-md-4">
                                        <label class="border rounded-3 p-3 h-100 d-block">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="mode" value="{{ $value }}" id="mode_{{ $value }}" @checked(old('mode', $defaultMode) === $value)>
                                                <label class="form-check-label fw-semibold" for="mode_{{ $value }}">{{ $label }}</label>
                                            </div>
                                            <div class="small text-muted">
                                                @if ($value === 'face')
                                                    Open camera.
                                                @elseif ($value === 'qr')
                                                    Scan or type code.
                                                @else
                                                    Tap from the grid.
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('mode')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Open Attendance Workspace</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
