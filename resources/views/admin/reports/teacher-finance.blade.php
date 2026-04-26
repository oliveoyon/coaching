@extends('layouts.admin')

@section('title', 'Teacher Finance')
@section('page-title', 'Teacher Finance')
@section('page-subtitle', 'Teacher-wise earnings, settlements, and outstanding payables.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.teacher-finance') }}" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="form-control">
                </div>
                <div class="col-lg-3">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classOptions as $class)
                            <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
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

    <div class="card page-card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher</th>
                            <th class="text-end">Earned In Month</th>
                            <th class="text-end">Settled In Month</th>
                            <th class="text-end">Outstanding Payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teacherSummaries as $summary)
                            <tr>
                                <td>{{ $summary['teacher_name'] }}</td>
                                <td class="text-end">{{ number_format((float) $summary['earned'], 2) }}</td>
                                <td class="text-end">{{ number_format((float) $summary['settled'], 2) }}</td>
                                <td class="text-end">{{ number_format((float) $summary['outstanding'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No teacher finance data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Teacher</th>
                            <th>Paid By</th>
                            <th>Note</th>
                            <th class="text-end">Settlement Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settlementHistory as $settlement)
                            <tr>
                                <td>{{ $settlement->settlement_date?->format('d M Y') }}</td>
                                <td>{{ $settlement->teacher?->user?->name }}</td>
                                <td>{{ $settlement->payer?->name ?: '-' }}</td>
                                <td>{{ $settlement->note ?: '-' }}</td>
                                <td class="text-end">{{ number_format((float) $settlement->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No settlement history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($settlementHistory->hasPages())
            <div class="card-footer bg-white">
                {{ $settlementHistory->links() }}
            </div>
        @endif
    </div>
@endsection
