@extends('layouts.admin')

@section('title', 'Due Reports')
@section('page-title', 'Due Reports')
@section('page-subtitle', 'Month-wise due list with student and guardian contact details.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.dues') }}" class="row g-3 align-items-end">
                <div class="col-lg-2">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                </div>
                <div class="col-lg-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Code, name, phone, guardian phone">
                </div>
                <div class="col-lg-2">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classOptions as $class)
                            <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="batch_id" class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select">
                        <option value="">All Batches</option>
                        @foreach ($batchOptions as $batch)
                            <option value="{{ $batch->id }}" @selected((string) $batchId === (string) $batch->id)>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="teacher_id" class="form-label">Teacher</label>
                    <select name="teacher_id" id="teacher_id" class="form-select" @disabled($teacherScopeId)>
                        <option value="">All Teachers</option>
                        @foreach ($teacherOptions as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) $selectedTeacherId === (string) $teacher->id)>{{ $teacher->user?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Code</th>
                            <th>Phone</th>
                            <th>Guardian Phone</th>
                            <th>Class</th>
                            <th>Batch</th>
                            <th>Fee Head</th>
                            <th>Frequency</th>
                            <th class="text-end">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dueRows as $row)
                            <tr>
                                <td>{{ $row['student_name'] }}</td>
                                <td>{{ $row['student_code'] }}</td>
                                <td>{{ $row['student_phone'] ?: '-' }}</td>
                                <td>{{ $row['guardian_phone'] ?: '-' }}</td>
                                <td>{{ $row['class_name'] }}</td>
                                <td>{{ $row['batch_name'] }}</td>
                                <td>{{ $row['fee_item'] }}</td>
                                <td>{{ ucfirst($row['fee_frequency'] ?? '-') }}</td>
                                <td class="text-end">{{ number_format((float) $row['remaining'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No due rows found for the selected month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
