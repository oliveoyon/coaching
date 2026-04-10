<div class="admin-card p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label fw-semibold">Name</label>
            <input id="name" name="name" type="text" class="form-control rounded-4" value="{{ old('name', $feeHead->name) }}" required>
            @error('name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="code" class="form-label fw-semibold">Code</label>
            <input id="code" name="code" type="text" class="form-control rounded-4" value="{{ old('code', $feeHead->code) }}" required>
            @error('code') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="type" class="form-label fw-semibold">Type</label>
            <select id="type" name="type" class="form-select rounded-4">
                @foreach (\App\Models\FeeHead::types() as $type)
                    <option value="{{ $type }}" @selected(old('type', $feeHead->type) === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            @error('type') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="frequency" class="form-label fw-semibold">Frequency</label>
            <select id="frequency" name="frequency" class="form-select rounded-4">
                @foreach (\App\Models\FeeHead::frequencies() as $frequency)
                    <option value="{{ $frequency }}" @selected(old('frequency', $feeHead->frequency) === $frequency)>{{ str($frequency)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            @error('frequency') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="4" class="form-control rounded-4">{{ old('description', $feeHead->description) }}</textarea>
            @error('description') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $feeHead->is_active))>
                <label class="form-check-label fw-semibold" for="is_active">Active fee head</label>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('fee-heads.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
