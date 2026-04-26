@extends('layouts.admin')

@section('title', 'Collect Payment')
@section('page-title', 'Collect Payment')
@section('page-subtitle', 'Search the student first, then collect multiple fee heads in one submit. The system will save separate payment rows underneath.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.payments.create') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label for="batch" class="form-label">Batch</label>
                    <select name="batch" id="batch" class="form-select">
                        <option value="">All active batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) $selectedBatchId === (string) $batch->id)>
                                {{ $batch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-5">
                    <label for="student_search" class="form-label">Student Search</label>
                    <input type="text" name="student_search" id="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Type student code, name, phone, guardian phone, or batch keyword">
                </div>

                <div class="col-lg-2">
                    <label for="month" class="form-label">Monthly Bill Month</label>
                    <input type="month" name="month" id="month" value="{{ $selectedMonth }}" class="form-control">
                </div>

                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Find</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Matching Students</h2>
                        <span class="text-muted small">{{ $students->count() }} found</span>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse ($students as $match)
                            <a href="{{ route('admin.payments.create', ['student' => $match->id, 'student_search' => $studentSearch, 'batch' => $selectedBatchId, 'month' => $selectedMonth]) }}"
                                class="list-group-item list-group-item-action rounded-3 mb-2 border {{ $student?->id === $match->id ? 'active border-primary' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $match->name }}</div>
                                        <div class="small {{ $student?->id === $match->id ? 'text-white-50' : 'text-muted' }}">
                                            {{ $match->student_code }} | {{ $match->phone ?: $match->guardian_phone }}
                                        </div>
                                        <div class="small {{ $student?->id === $match->id ? 'text-white-50' : 'text-muted' }}">
                                            Class {{ $match->academicClass?->name }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $student?->id === $match->id ? 'text-bg-light' : 'text-bg-secondary' }}">Select</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-muted">
                                Search by student ID, phone, guardian phone, name, or batch keyword. A code like <strong>003</strong> will match any student with that pattern.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card page-card">
                <div class="card-body p-4">
                    @if ($student)
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                            <div>
                                <h2 class="h4 mb-1">{{ $student->name }}</h2>
                                <div class="text-muted">
                                    {{ $student->student_code }} | Student {{ $student->phone ?: '-' }} | Guardian {{ $student->guardian_phone }}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.student-profiles.show', $student) }}" class="btn btn-outline-secondary">Full Profile</a>
                                <span class="badge text-bg-light border align-self-center">Month {{ $selectedMonth }}</span>
                            </div>
                        </div>

                        @if ($errors->has('items'))
                            <div class="alert alert-danger">{{ $errors->first('items') }}</div>
                        @endif

                        @if ($collectionRows->isNotEmpty())
                            <form method="POST" action="{{ route('admin.payments.store') }}">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="month_submit" class="form-label">Monthly Bill Month</label>
                                        <input type="month" name="month" id="month_submit" value="{{ old('month', $selectedMonth) }}" class="form-control @error('month') is-invalid @enderror">
                                        <div class="form-text">Used for monthly fee heads only.</div>
                                        @error('month')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="payment_date" class="form-label">Payment Date</label>
                                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" class="form-control @error('payment_date') is-invalid @enderror" required>
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="method" class="form-label">Payment Method</label>
                                        <select name="method" id="method" class="form-select @error('method') is-invalid @enderror" required>
                                            <option value="cash" @selected(old('method', 'cash') === 'cash')>Cash</option>
                                            <option value="bkash" @selected(old('method') === 'bkash')>bKash</option>
                                            <option value="nagad" @selected(old('method') === 'nagad')>Nagad</option>
                                        </select>
                                        @error('method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="transaction_id" class="form-label">Transaction ID</label>
                                        <input type="text" name="transaction_id" id="transaction_id" value="{{ old('transaction_id') }}" class="form-control @error('transaction_id') is-invalid @enderror">
                                        <div class="form-text">Keep empty for cash. For bKash or Nagad this single transaction ID will be attached to each submitted fee line.</div>
                                        @error('transaction_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-4">
                                    @foreach ($collectionRows as $group)
                                        <div class="border rounded-3 overflow-hidden">
                                            <div class="bg-light px-3 py-3 border-bottom">
                                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-2">
                                                    <div>
                                                        <div class="fw-semibold">{{ $group['enrollment']->batch?->name }}</div>
                                                        <div class="small text-muted">
                                                            {{ $group['enrollment']->batch?->academicClass?->name }}
                                                            @if ($group['enrollment']->batch?->subject)
                                                                | {{ $group['enrollment']->batch?->subject?->name }}
                                                            @endif
                                                            | Start {{ $group['enrollment']->start_date?->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-muted small">
                                                        Enrollment #{{ $group['enrollment']->id }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Fee Head</th>
                                                            <th>Frequency</th>
                                                            <th>Fee</th>
                                                            <th>Approved</th>
                                                            <th>Pending</th>
                                                            <th>Remaining</th>
                                                            <th style="min-width: 160px;">Collect Now</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($group['fees'] as $index => $feeRow)
                                                            @php($rowKey = $loop->parent->index.'_'.$index)
                                                            <tr>
                                                                <td>
                                                                    <div class="fw-semibold">{{ $feeRow['batch_fee']->feeType?->name }}</div>
                                                                    @if ($feeRow['is_monthly'])
                                                                        <div class="small text-muted">{{ old('month', $selectedMonth) }}</div>
                                                                    @endif
                                                                    <input type="hidden" name="items[{{ $rowKey }}][enrollment_id]" value="{{ $group['enrollment']->id }}">
                                                                    <input type="hidden" name="items[{{ $rowKey }}][batch_fee_id]" value="{{ $feeRow['batch_fee']->id }}">
                                                                </td>
                                                                <td>{{ ucfirst(str_replace('_', ' ', $feeRow['batch_fee']->feeType?->frequency ?? '')) }}</td>
                                                                <td>{{ number_format((float) $feeRow['batch_fee']->amount, 2) }}</td>
                                                                <td>{{ number_format($feeRow['summary']['approved'], 2) }}</td>
                                                                <td>{{ number_format($feeRow['summary']['pending'], 2) }}</td>
                                                                <td>
                                                                    <span class="fw-semibold {{ $feeRow['summary']['remaining'] > 0 ? 'text-danger' : 'text-success' }}">
                                                                        {{ number_format($feeRow['summary']['remaining'], 2) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        type="number"
                                                                        step="0.01"
                                                                        name="items[{{ $rowKey }}][amount]"
                                                                        value="{{ old("items.{$rowKey}.amount", $feeRow['summary']['remaining'] > 0 ? number_format($feeRow['summary']['remaining'], 2, '.', '') : '0.00') }}"
                                                                        class="form-control @error("items.{$rowKey}.amount") is-invalid @enderror"
                                                                    >
                                                                    @error("items.{$rowKey}.amount")
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Submit Collection</button>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-info mb-0">This student has no active fee items to collect with the current filters.</div>
                        @endif
                    @else
                        <div class="text-muted">
                            Search and select a student from the left side. After that, all remaining fee heads for the student will appear here with auto-filled amounts so the collector can submit once.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
