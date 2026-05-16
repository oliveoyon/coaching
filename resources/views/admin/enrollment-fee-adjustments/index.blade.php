@extends('layouts.admin')

@section('title', 'Fee Adjustments')
@section('page-title', 'Fee Adjustments')
@section('page-subtitle', 'Discount and waiver setup')

@section('content')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="fw-semibold mb-1">{{ $enrollment->student?->name }}</div>
                    <div class="small text-muted mb-4">
                        {{ $enrollment->student?->student_code }} | {{ $enrollment->batch?->name }}
                    </div>

                    <form method="POST" action="{{ route('admin.enrollment-fee-adjustments.store', $enrollment) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="batch_fee_id" class="form-label">Fee Item</label>
                            <select name="batch_fee_id" id="batch_fee_id" class="form-select @error('batch_fee_id') is-invalid @enderror" required>
                                <option value="">Select fee item</option>
                                @foreach ($batchFees as $batchFee)
                                    <option value="{{ $batchFee->id }}" @selected((string) old('batch_fee_id') === (string) $batchFee->id)>
                                        {{ $batchFee->feeType?->name }} ({{ number_format((float) $batchFee->amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('batch_fee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="adjustment_type" class="form-label">Adjustment</label>
                                <select name="adjustment_type" id="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                    <option value="discount" @selected(old('adjustment_type', 'discount') === 'discount')>Discount</option>
                                    <option value="waiver" @selected(old('adjustment_type') === 'waiver')>Waiver</option>
                                </select>
                                @error('adjustment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="value_type" class="form-label">Value Type</label>
                                <select name="value_type" id="value_type" class="form-select @error('value_type') is-invalid @enderror" required>
                                    <option value="fixed" @selected(old('value_type', 'fixed') === 'fixed')>Fixed</option>
                                    <option value="percent" @selected(old('value_type') === 'percent')>Percent</option>
                                </select>
                                @error('value_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="value" class="form-label">Value</label>
                                <input type="number" step="0.01" name="value" id="value" value="{{ old('value') }}" class="form-control @error('value') is-invalid @enderror" required>
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="effective_from_month" class="form-label">Start Month</label>
                                <input type="month" name="effective_from_month" id="effective_from_month" value="{{ old('effective_from_month') }}" class="form-control @error('effective_from_month') is-invalid @enderror">
                                @error('effective_from_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="effective_to_month" class="form-label">End Month</label>
                                <input type="month" name="effective_to_month" id="effective_to_month" value="{{ old('effective_to_month') }}" class="form-control @error('effective_to_month') is-invalid @enderror">
                                @error('effective_to_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">Add Adjustment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card page-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Current Adjustments</h2>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee</th>
                                    <th>Adjustment</th>
                                    <th>Months</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($enrollment->feeAdjustments as $adjustment)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $adjustment->batchFee?->feeType?->name }}</div>
                                            <div class="small text-muted">{{ $adjustment->note ?: '-' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ ucfirst($adjustment->adjustment_type) }}</div>
                                            <div class="small text-muted">
                                                {{ number_format((float) $adjustment->value, 2) }} {{ $adjustment->value_type === 'percent' ? '%' : 'BDT' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $adjustment->effective_from_month ?: 'Now' }}</div>
                                            <div class="small text-muted">to {{ $adjustment->effective_to_month ?: 'Open' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $adjustment->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                {{ ucfirst($adjustment->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.enrollment-fee-adjustments.update', [$enrollment, $adjustment]) }}" class="row g-2 justify-content-end">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-auto">
                                                    <input type="hidden" name="batch_fee_id" value="{{ $adjustment->batch_fee_id }}">
                                                    <input type="hidden" name="adjustment_type" value="{{ $adjustment->adjustment_type }}">
                                                    <input type="hidden" name="value_type" value="{{ $adjustment->value_type }}">
                                                    <input type="hidden" name="effective_from_month" value="{{ $adjustment->effective_from_month }}">
                                                    <input type="hidden" name="effective_to_month" value="{{ $adjustment->effective_to_month }}">
                                                    <input type="hidden" name="note" value="{{ $adjustment->note }}">
                                                    <input type="hidden" name="value" value="{{ $adjustment->value }}">
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="active" @selected($adjustment->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($adjustment->status === 'inactive')>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No adjustment added for this enrollment.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
