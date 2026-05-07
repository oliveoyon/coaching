@extends('layouts.admin')

@section('title', 'Batch Fee Setup')
@section('page-title', 'Batch Fee Setup')
@section('page-subtitle', 'Set fees for this batch')

@section('content')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="fw-semibold mb-1">{{ $batch->name }}</div>
                    <div class="small text-muted mb-4">
                        {{ $batch->academicClass?->name }}
                        @if ($batch->subject)
                            | {{ $batch->subject?->name }}
                        @endif
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small">Monthly Fee</div>
                                <div class="fs-4 fw-semibold mt-1">{{ number_format((float) $batch->monthly_fee, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small">Configured Fee Items</div>
                                <div class="fs-4 fw-semibold mt-1">{{ $batch->batchFees->count() }}</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.batch-fees.store', $batch) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="fee_type_id" class="form-label">Fee Type</label>
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

                        <div class="mb-4">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="border rounded-4 p-3 bg-light-subtle mb-4">
                            <div class="fw-semibold mb-2">Common Example</div>
                            <div class="small text-muted">Admission Fee = One Time, Tuition Fee = Monthly, Exam Fee = Manual.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Fee</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card page-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Current Fees</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batch->batchFees as $batchFee)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $batchFee->feeType?->name }}</div>
                                            <div class="small text-muted">{{ $batchFee->feeType?->code }}</div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $batchFee->feeType?->frequency === 'monthly' ? 'text-bg-primary' : ($batchFee->feeType?->frequency === 'one_time' ? 'text-bg-success' : 'text-bg-warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $batchFee->feeType?->frequency ?? '')) }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format((float) $batchFee->amount, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill {{ $batchFee->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                {{ ucfirst($batchFee->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.batch-fees.update', [$batch, $batchFee]) }}" class="row g-2 justify-content-end">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-auto">
                                                    <input type="hidden" name="fee_type_id" value="{{ $batchFee->fee_type_id }}">
                                                    <input type="number" step="0.01" name="amount" value="{{ $batchFee->amount }}" class="form-control form-control-sm" style="width: 110px;">
                                                </div>
                                                <div class="col-auto">
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="active" @selected($batchFee->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($batchFee->status === 'inactive')>Inactive</option>
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
                                        <td colspan="5" class="text-center py-5 text-muted">No fee set for this batch.</td>
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
