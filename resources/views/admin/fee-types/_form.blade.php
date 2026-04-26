@csrf

<div class="row g-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Fee Type Name</label>
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
        <label for="frequency" class="form-label">Frequency</label>
        <select name="frequency" id="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
            <option value="monthly" @selected(old('frequency', $feeType->frequency ?? 'monthly') === 'monthly')>Monthly</option>
            <option value="one_time" @selected(old('frequency', $feeType->frequency ?? 'monthly') === 'one_time')>One Time</option>
            <option value="manual" @selected(old('frequency', $feeType->frequency ?? 'monthly') === 'manual')>Manual</option>
        </select>
        @error('frequency')
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

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.fee-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
