<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="admin-card p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label fw-semibold">Title</label>
                    <input id="title" name="title" type="text" class="form-control rounded-4" value="{{ old('title', $feeStructure->title) }}" required>
                    @error('title') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="fee_head_id" class="form-label fw-semibold">Fee Head</label>
                    <select id="fee_head_id" name="fee_head_id" class="form-select rounded-4" required>
                        <option value="">Select fee head</option>
                        @foreach ($feeHeads as $feeHead)
                            <option value="{{ $feeHead->id }}" @selected((string) old('fee_head_id', $feeStructure->fee_head_id) === (string) $feeHead->id)>{{ $feeHead->name }} ({{ $feeHead->code }})</option>
                        @endforeach
                    </select>
                    @error('fee_head_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="billing_model" class="form-label fw-semibold">Billing Model Match</label>
                    <select id="billing_model" name="billing_model" class="form-select rounded-4">
                        <option value="">All models</option>
                        @foreach ($billingModels as $value => $label)
                            <option value="{{ $value }}" @selected(old('billing_model', $feeStructure->billing_model) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('billing_model') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="amount" class="form-label fw-semibold">Amount</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0" class="form-control rounded-4" value="{{ old('amount', $feeStructure->amount) }}" required>
                    @error('amount') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="applicable_type" class="form-label fw-semibold">Applies To</label>
                    <select id="applicable_type" name="applicable_type" class="form-select rounded-4">
                        <option value="{{ \App\Models\FeeStructure::APPLICABLE_TENANT }}" @selected(old('applicable_type', $feeStructure->applicable_type) === \App\Models\FeeStructure::APPLICABLE_TENANT)>Tenant</option>
                        <option value="{{ \App\Models\FeeStructure::APPLICABLE_PROGRAM }}" @selected(old('applicable_type', $feeStructure->applicable_type) === \App\Models\FeeStructure::APPLICABLE_PROGRAM)>Program</option>
                        <option value="{{ \App\Models\FeeStructure::APPLICABLE_BATCH }}" @selected(old('applicable_type', $feeStructure->applicable_type) === \App\Models\FeeStructure::APPLICABLE_BATCH)>Batch</option>
                        <option value="{{ \App\Models\FeeStructure::APPLICABLE_COURSE }}" @selected(old('applicable_type', $feeStructure->applicable_type) === \App\Models\FeeStructure::APPLICABLE_COURSE)>Course (Future Ready)</option>
                    </select>
                    @error('applicable_type') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="applicable_id" class="form-label fw-semibold">Reference ID</label>
                    <input id="applicable_id" name="applicable_id" type="number" min="1" class="form-control rounded-4" value="{{ old('applicable_id', $feeStructure->applicable_id) }}">
                    <div class="form-text">Use a program or batch ID for current modules. Course IDs can use the same field later.</div>
                    @error('applicable_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Available Programs</label>
                    <div class="form-control rounded-4 bg-body-tertiary">{{ $programs->pluck('name', 'id')->map(fn ($name, $id) => $id.': '.$name)->join(' | ') ?: 'No active programs' }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Available Batches</label>
                    <div class="form-control rounded-4 bg-body-tertiary">{{ $batches->map(fn ($batch) => $batch->id.': '.$batch->name)->join(' | ') ?: 'No active batches' }}</div>
                </div>
                <div class="col-md-6">
                    <label for="starts_on" class="form-label fw-semibold">Starts On</label>
                    <input id="starts_on" name="starts_on" type="date" class="form-control rounded-4" value="{{ old('starts_on', optional($feeStructure->starts_on)->format('Y-m-d')) }}">
                    @error('starts_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="ends_on" class="form-label fw-semibold">Ends On</label>
                    <input id="ends_on" name="ends_on" type="date" class="form-control rounded-4" value="{{ old('ends_on', optional($feeStructure->ends_on)->format('Y-m-d')) }}">
                    @error('ends_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="form-control rounded-4">{{ old('notes', $feeStructure->notes) }}</textarea>
                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $feeStructure->is_active))>
                        <label class="form-check-label fw-semibold" for="is_active">Active structure</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="admin-card p-4">
            <div class="page-section-title text-warning-emphasis">Design Note</div>
            <p class="text-secondary mb-0">This module only defines charge configuration. Actual due generation and payment collection will resolve these structures later through services, not by hardcoding one billing strategy into student records.</p>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('fee-structures.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">{{ $submitLabel }}</button>
</div>
