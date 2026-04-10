@extends('layouts.app')

@section('title', 'Billing Settings')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Billing Settings</h1>
        <p class="text-secondary mb-0">Configure how this tenant should be billed without tying pricing logic directly to students or enrollments.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        @php($config = $billingConfig->config ?? [])

        <form method="POST" action="{{ route('billing-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="admin-card p-4">
                        <div class="page-section-title text-info-emphasis">Tenant Policy</div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label for="billing_model" class="form-label fw-semibold">Billing Model</label>
                                <select id="billing_model" name="billing_model" class="form-select rounded-4">
                                    <option value="{{ \App\Models\Tenant::BILLING_MODEL_PER_STUDENT }}" @selected(old('billing_model', $billingConfig->billing_model) === \App\Models\Tenant::BILLING_MODEL_PER_STUDENT)>Per Student</option>
                                    <option value="{{ \App\Models\Tenant::BILLING_MODEL_PER_COURSE }}" @selected(old('billing_model', $billingConfig->billing_model) === \App\Models\Tenant::BILLING_MODEL_PER_COURSE)>Per Course</option>
                                    <option value="{{ \App\Models\Tenant::BILLING_MODEL_PER_BATCH }}" @selected(old('billing_model', $billingConfig->billing_model) === \App\Models\Tenant::BILLING_MODEL_PER_BATCH)>Per Batch</option>
                                </select>
                                @error('billing_model') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="billing_period" class="form-label fw-semibold">Billing Period</label>
                                <select id="billing_period" name="billing_period" class="form-select rounded-4">
                                    <option value="monthly" @selected(old('billing_period', $config['billing_period'] ?? 'monthly') === 'monthly')>Monthly</option>
                                    <option value="custom" @selected(old('billing_period', $config['billing_period'] ?? 'monthly') === 'custom')>Custom</option>
                                </select>
                                @error('billing_period') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Current Tenant</label>
                                <div class="form-control rounded-4 bg-body-tertiary">{{ $tenant->display_name }}</div>
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">Policy Notes</label>
                                <textarea id="notes" name="notes" rows="4" class="form-control rounded-4">{{ old('notes', $config['notes'] ?? null) }}</textarea>
                                @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="admin-card p-4">
                        <div class="page-section-title text-warning-emphasis">Resolution Hints</div>
                        <div class="vstack gap-3 mt-2">
                            <div class="border rounded-4 p-3">
                                <input type="hidden" name="unique_student_per_period" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="unique_student_per_period" value="1" id="unique_student_per_period" @checked(old('unique_student_per_period', $config['unique_student_per_period'] ?? false))>
                                    <label class="form-check-label fw-semibold" for="unique_student_per_period">Unique student once per period</label>
                                </div>
                                <div class="small text-secondary mt-2">Useful for `per_student` tenants that want one monthly charge regardless of multiple enrollments.</div>
                            </div>

                            <div class="border rounded-4 p-3">
                                <input type="hidden" name="count_each_batch_separately" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="count_each_batch_separately" value="1" id="count_each_batch_separately" @checked(old('count_each_batch_separately', $config['count_each_batch_separately'] ?? false))>
                                    <label class="form-check-label fw-semibold" for="count_each_batch_separately">Count each batch separately</label>
                                </div>
                                <div class="small text-secondary mt-2">Useful when batch-wise billing should not merge multiple enrollments.</div>
                            </div>

                            <div class="border rounded-4 p-3">
                                <input type="hidden" name="count_each_course_separately" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="count_each_course_separately" value="1" id="count_each_course_separately" @checked(old('count_each_course_separately', $config['count_each_course_separately'] ?? false))>
                                    <label class="form-check-label fw-semibold" for="count_each_course_separately">Count each course separately</label>
                                </div>
                                <div class="small text-secondary mt-2">Course-wise charging will plug into the same policy once the course module is introduced.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Save Billing Settings</button>
            </div>
        </form>
    </div>
@endsection
