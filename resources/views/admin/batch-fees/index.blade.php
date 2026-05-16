@extends('layouts.admin')

@section('title', 'Batch Fee Setup')
@section('page-title', 'Batch Fee Setup')
@section('page-subtitle', 'Manage this batch clearly.')

@section('content')
    @php
        $monthlyBatchFees = $batch->batchFees
            ->filter(fn ($item) => $item->feeType?->frequency === 'monthly')
            ->sortBy(fn ($item) => $item->feeType?->name);

        $allMonthOverrides = $batch->batchFees
            ->flatMap(fn ($item) => $item->monthOverrides->map(fn ($override) => ['batch_fee' => $item, 'override' => $override]))
            ->sortBy([
                fn ($item) => $item['override']->month,
                fn ($item) => $item['batch_fee']->feeType?->name,
            ])
            ->values();
    @endphp

    <style>
        .batch-fee-page .summary-card {
            border: 0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            border-radius: 1rem;
            height: 100%;
        }

        .batch-fee-page .summary-card .card-body {
            padding: 1rem 1.05rem;
        }

        .batch-fee-page .summary-card .label {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .batch-fee-page .summary-card .value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.15;
        }

        .batch-fee-page .section-card {
            border-radius: 1rem;
        }

        .batch-fee-page .section-card .card-body {
            padding: 1rem 1.05rem;
        }

        .batch-fee-page .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.9rem;
            color: #0f172a;
        }

        .batch-fee-page .mini-stack {
            display: grid;
            gap: 0.75rem;
        }

        .batch-fee-page .mini-item {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.9rem;
            padding: 0.85rem 0.9rem;
            background: #fff;
        }

        .batch-fee-page .fee-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            padding: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .batch-fee-page .fee-card + .fee-card {
            margin-top: 0.9rem;
        }

        .batch-fee-page .fee-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.9rem;
            margin-bottom: 0.9rem;
        }

        .batch-fee-page .fee-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.35rem;
        }

        .batch-fee-page .fee-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.6rem;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .batch-fee-page .helper-note {
            font-size: 0.8rem;
            color: #64748b;
        }

        .batch-fee-page .compact-label {
            font-size: 0.8rem;
        }
    </style>

    <div class="batch-fee-page">
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                    <div class="card-body">
                        <div class="label text-primary">Batch</div>
                        <div class="value">{{ $batch->name }}</div>
                        <div class="small text-muted mt-1">
                            {{ $batch->academicClass?->name }}
                            @if ($batch->subject)
                                | {{ $batch->subject?->name }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                    <div class="card-body">
                        <div class="label text-success">Fee Items</div>
                        <div class="value">{{ $batch->batchFees->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                    <div class="card-body">
                        <div class="label text-warning">Paused Months</div>
                        <div class="value">{{ $batch->billingBreaks->where('status', 'active')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
                    <div class="card-body">
                        <div class="label" style="color:#7c3aed;">Special Months</div>
                        <div class="value">{{ $allMonthOverrides->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="card page-card section-card mb-4">
                    <div class="card-body">
                        <div class="section-title">Add Fee</div>

                        <form method="POST" action="{{ route('admin.batch-fees.store', $batch) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="fee_type_id" class="form-label compact-label">Fee Type</label>
                                <select name="fee_type_id" id="fee_type_id" class="form-select @error('fee_type_id') is-invalid @enderror" required>
                                    <option value="">Select fee type</option>
                                    @foreach ($feeTypes as $feeType)
                                        <option value="{{ $feeType->id }}" @selected((string) old('fee_type_id') === (string) $feeType->id)>
                                            {{ $feeType->name }} ({{ ucfirst(str_replace('_', ' ', $feeType->frequency)) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('fee_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label compact-label">Amount</label>
                                <input type="number" step="0.01" min="0" name="amount" id="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label for="effective_from_month" class="form-label compact-label">Start Month</label>
                                    <input type="month" name="effective_from_month" id="effective_from_month" value="{{ old('effective_from_month') }}" class="form-control @error('effective_from_month') is-invalid @enderror">
                                    @error('effective_from_month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="effective_to_month" class="form-label compact-label">End Month</label>
                                    <input type="month" name="effective_to_month" id="effective_to_month" value="{{ old('effective_to_month') }}" class="form-control @error('effective_to_month') is-invalid @enderror">
                                    @error('effective_to_month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label compact-label">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="helper-note mb-3">Use amount 0 only for an intentionally free fee.</div>

                            <button type="submit" class="btn btn-primary w-100">Add Fee</button>
                        </form>
                    </div>
                </div>

                <div class="card page-card section-card mb-4">
                    <div class="card-body">
                        <div class="section-title">Paused Months</div>

                        <form method="POST" action="{{ route('admin.batch-fees.billing-breaks.store', $batch) }}" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label for="billing_break_month" class="form-label compact-label">Month</label>
                                <input type="month" name="month" id="billing_break_month" value="{{ old('month') }}" class="form-control @error('month') is-invalid @enderror" required>
                                @error('month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="billing_break_note" class="form-label compact-label">Note</label>
                                <input type="text" name="note" id="billing_break_note" value="{{ old('note') }}" class="form-control @error('note') is-invalid @enderror" placeholder="Vacation or holiday">
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100">Add Paused Month</button>
                        </form>

                        <div class="mini-stack">
                            @forelse ($batch->billingBreaks->where('status', 'active')->sortBy('month') as $billingBreak)
                                <div class="mini-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $billingBreak->month)->format('M Y') }}</div>
                                            <div class="small text-muted">{{ $billingBreak->note ?: 'Paused for this month' }}</div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.batch-fees.billing-breaks.destroy', [$batch, $billingBreak]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="small text-muted">No paused month set.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card page-card section-card">
                    <div class="card-body">
                        <div class="section-title">Special Month Amount</div>

                        @if ($monthlyBatchFees->isNotEmpty())
                            <form method="POST" action="{{ route('admin.batch-fees.month-overrides.store', $batch) }}" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="batch_fee_id" class="form-label compact-label">Monthly Fee</label>
                                    <select name="batch_fee_id" id="batch_fee_id" class="form-select @error('batch_fee_id') is-invalid @enderror" required>
                                        <option value="">Select monthly fee</option>
                                        @foreach ($monthlyBatchFees as $monthlyFee)
                                            <option value="{{ $monthlyFee->id }}" @selected((string) old('batch_fee_id') === (string) $monthlyFee->id)>
                                                {{ $monthlyFee->feeType?->name }} | Base {{ number_format((float) $monthlyFee->amount, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('batch_fee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label for="override_month" class="form-label compact-label">Month</label>
                                        <input type="month" name="month" id="override_month" value="{{ old('month') }}" class="form-control @error('month') is-invalid @enderror" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="override_amount" class="form-label compact-label">Amount</label>
                                        <input type="number" step="0.01" min="0" name="amount" id="override_amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="override_note" class="form-label compact-label">Note</label>
                                    <input type="text" name="note" id="override_note" value="{{ old('note') }}" class="form-control @error('note') is-invalid @enderror" placeholder="August discount">
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">Save Special Amount</button>
                            </form>
                        @else
                            <div class="small text-muted mb-3">Add a monthly fee first.</div>
                        @endif

                        <div class="mini-stack">
                            @forelse ($allMonthOverrides as $item)
                                <div class="mini-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $item['batch_fee']->feeType?->name }} | {{ \Carbon\Carbon::createFromFormat('Y-m', $item['override']->month)->format('M Y') }}</div>
                                            <div class="small text-muted">Amount {{ number_format((float) $item['override']->amount, 2) }}</div>
                                            @if ($item['override']->note)
                                                <div class="small text-muted">{{ $item['override']->note }}</div>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.batch-fees.month-overrides.destroy', [$batch, $item['override']]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="small text-muted">No special month amount set.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card page-card section-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="section-title mb-0">Current Fees</div>
                            <span class="small text-muted">{{ $batch->batchFees->count() }} item{{ $batch->batchFees->count() === 1 ? '' : 's' }}</span>
                        </div>

                        @forelse ($batch->batchFees->sortBy([fn ($item) => $item->feeType?->name, fn ($item) => $item->effective_from_month ?: '']) as $batchFee)
                            <div class="fee-card">
                                <div class="fee-head">
                                    <div>
                                        <div class="fw-semibold fs-6">{{ $batchFee->feeType?->name }}</div>
                                        <div class="fee-meta">
                                            <span class="fee-pill">{{ ucfirst(str_replace('_', ' ', $batchFee->feeType?->frequency ?? '')) }}</span>
                                            <span class="fee-pill">{{ $batchFee->effective_from_month ?: 'Now' }} to {{ $batchFee->effective_to_month ?: 'Open' }}</span>
                                            <span class="fee-pill">{{ ucfirst($batchFee->status) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold fs-5">{{ number_format((float) $batchFee->amount, 2) }}</div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.batch-fees.update', [$batch, $batchFee]) }}">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="fee_type_id" value="{{ $batchFee->fee_type_id }}">

                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label compact-label">Amount</label>
                                            <input type="number" step="0.01" min="0" name="amount" value="{{ number_format((float) $batchFee->amount, 2, '.', '') }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label compact-label">Apply from Month</label>
                                            <input type="month" name="apply_from_month" value="" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label compact-label">Start Month</label>
                                            <input type="month" name="effective_from_month" value="{{ $batchFee->effective_from_month }}" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label compact-label">End Month</label>
                                            <input type="month" name="effective_to_month" value="{{ $batchFee->effective_to_month }}" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label compact-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" @selected($batchFee->status === 'active')>Active</option>
                                                <option value="inactive" @selected($batchFee->status === 'inactive')>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-12 d-flex justify-content-between align-items-center gap-3">
                                            <div class="helper-note">Use “Apply from Month” only when the new amount should start later.</div>
                                            <button type="submit" class="btn btn-outline-primary">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">No fee set for this batch.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
