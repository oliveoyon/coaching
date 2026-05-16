@csrf

<style>
    .fee-type-form .section-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .fee-type-form .section-card + .section-card {
        margin-top: 1rem;
    }

    .fee-type-form .section-head {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1rem;
    }

    .fee-type-form .frequency-option .btn {
        border-radius: 1rem;
        padding: 1rem;
        border-color: rgba(59, 130, 246, 0.18);
        background: #fff;
        color: #0f172a;
    }

    .fee-type-form .frequency-option .btn .small {
        color: #64748b !important;
    }

    .fee-type-form .btn-check:checked + .btn {
        background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
        border-color: #60a5fa;
        color: #1d4ed8;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.12);
    }

    .fee-type-form .btn-check:checked + .btn .small {
        color: #1e40af !important;
    }
</style>

<div class="fee-type-form">
<div class="section-card p-4 p-lg-4">
    <div class="section-head">Fee Info</div>
    <div class="row g-4">
        <div class="col-md-6">
            <label for="name" class="form-label">Fee Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $feeType->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="code" class="form-label">Code</label>
            <input type="text" name="code" id="code" value="{{ old('code', $feeType->code ?? '') }}" class="form-control @error('code') is-invalid @enderror" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" @selected(old('status', $feeType->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $feeType->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="section-card p-4 p-lg-4">
    <div class="section-head">How This Fee Works</div>
    <div class="row g-3">
        @php($selectedFrequency = old('frequency', $feeType->frequency ?? 'monthly'))
        @foreach ([
            'monthly' => ['label' => 'Monthly', 'text' => 'Used for tuition or any fee collected every month.'],
            'one_time' => ['label' => 'One Time', 'text' => 'Used for admission or other fee collected once.'],
            'manual' => ['label' => 'Manual', 'text' => 'Used for exam or occasional fee when needed.'],
        ] as $value => $option)
            <div class="col-md-4 frequency-option">
                <input class="btn-check" type="radio" name="frequency" id="frequency_{{ $value }}" value="{{ $value }}" @checked($selectedFrequency === $value) required>
                <label class="btn btn-outline-primary text-start w-100 h-100" for="frequency_{{ $value }}">
                    <div class="fw-semibold">{{ $option['label'] }}</div>
                    <div class="small text-muted">{{ $option['text'] }}</div>
                </label>
            </div>
        @endforeach
        @error('frequency')
            <div class="col-12">
                <div class="invalid-feedback d-block">{{ $message }}</div>
            </div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.fee-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
</div>
