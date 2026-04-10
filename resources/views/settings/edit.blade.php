@extends('layouts.app')

@section('title', 'Tenant Settings')

@section('page_header')
    <div>
        <div class="page-section-title">Administration</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Tenant Settings</h1>
        <p class="text-secondary mb-0">Manage profile, billing, communication, and post-payment behavior from one place.</p>
    </div>
@endsection

@section('content')
    @php($billing = $billingConfig->config ?? [])
    @php($postPayment = $postPaymentSettings ?? [])
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-3 mb-4">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item"><a href="{{ route('settings.edit', ['tab' => 'profile']) }}" class="nav-link rounded-pill {{ $activeTab === 'profile' ? 'active' : '' }}">Profile</a></li>
                <li class="nav-item"><a href="{{ route('settings.edit', ['tab' => 'billing']) }}" class="nav-link rounded-pill {{ $activeTab === 'billing' ? 'active' : '' }}">Billing</a></li>
                <li class="nav-item"><a href="{{ route('settings.edit', ['tab' => 'communication']) }}" class="nav-link rounded-pill {{ $activeTab === 'communication' ? 'active' : '' }}">Communication</a></li>
                <li class="nav-item"><a href="{{ route('settings.edit', ['tab' => 'post-payment']) }}" class="nav-link rounded-pill {{ $activeTab === 'post-payment' ? 'active' : '' }}">Post Payment</a></li>
            </ul>
        </div>

        @if ($activeTab === 'profile')
            <form method="POST" action="{{ route('settings.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="admin-card p-4">
                    <div class="page-section-title text-primary-emphasis">Tenant Profile</div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Display Name</label>
                            <input id="name" name="name" type="text" class="form-control rounded-4" value="{{ old('name', $tenant->name) }}" required>
                            @error('name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="legal_name" class="form-label fw-semibold">Legal Name</label>
                            <input id="legal_name" name="legal_name" type="text" class="form-control rounded-4" value="{{ old('legal_name', $tenant->legal_name) }}">
                            @error('legal_name') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="contact_person" class="form-label fw-semibold">Contact Person</label>
                            <input id="contact_person" name="contact_person" type="text" class="form-control rounded-4" value="{{ old('contact_person', $tenant->contact_person) }}">
                            @error('contact_person') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label fw-semibold">Phone</label>
                            <input id="phone" name="phone" type="text" class="form-control rounded-4" value="{{ old('phone', $tenant->phone) }}">
                            @error('phone') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" name="email" type="email" class="form-control rounded-4" value="{{ old('email', $tenant->email) }}">
                            @error('email') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="website" class="form-label fw-semibold">Website</label>
                            <input id="website" name="website" type="url" class="form-control rounded-4" value="{{ old('website', $tenant->website) }}">
                            @error('website') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="timezone" class="form-label fw-semibold">Timezone</label>
                            <input id="timezone" name="timezone" type="text" class="form-control rounded-4" value="{{ old('timezone', $tenant->timezone) }}" required>
                            @error('timezone') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="currency" class="form-label fw-semibold">Currency</label>
                            <input id="currency" name="currency" type="text" class="form-control rounded-4" value="{{ old('currency', $tenant->currency) }}" required>
                            @error('currency') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold">Address</label>
                            <textarea id="address" name="address" rows="3" class="form-control rounded-4">{{ old('address', $tenant->address) }}</textarea>
                            @error('address') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label fw-semibold">City</label>
                            <input id="city" name="city" type="text" class="form-control rounded-4" value="{{ old('city', $tenant->city) }}">
                            @error('city') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label fw-semibold">State</label>
                            <input id="state" name="state" type="text" class="form-control rounded-4" value="{{ old('state', $tenant->state) }}">
                            @error('state') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label fw-semibold">Country</label>
                            <input id="country" name="country" type="text" class="form-control rounded-4" value="{{ old('country', $tenant->country) }}">
                            @error('country') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Save Profile Settings</button>
                </div>
            </form>
        @endif

        @if ($activeTab === 'billing')
            <form method="POST" action="{{ route('settings.billing.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="admin-card p-4">
                            <div class="page-section-title text-info-emphasis">Billing Policy</div>
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
                                        <option value="monthly" @selected(old('billing_period', $billing['billing_period'] ?? 'monthly') === 'monthly')>Monthly</option>
                                        <option value="custom" @selected(old('billing_period', $billing['billing_period'] ?? 'monthly') === 'custom')>Custom</option>
                                    </select>
                                    @error('billing_period') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label fw-semibold">Notes</label>
                                    <input id="notes" name="notes" type="text" class="form-control rounded-4" value="{{ old('notes', $billing['notes'] ?? '') }}">
                                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="admin-card p-4">
                            <div class="page-section-title text-warning-emphasis">Resolution Rules</div>
                            <div class="vstack gap-3 mt-2">
                                <div class="border rounded-4 p-3">
                                    <input type="hidden" name="unique_student_per_period" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="unique_student_per_period" name="unique_student_per_period" value="1" @checked(old('unique_student_per_period', $billing['unique_student_per_period'] ?? false))>
                                        <label class="form-check-label fw-semibold" for="unique_student_per_period">Unique student once per period</label>
                                    </div>
                                </div>
                                <div class="border rounded-4 p-3">
                                    <input type="hidden" name="count_each_batch_separately" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="count_each_batch_separately" name="count_each_batch_separately" value="1" @checked(old('count_each_batch_separately', $billing['count_each_batch_separately'] ?? false))>
                                        <label class="form-check-label fw-semibold" for="count_each_batch_separately">Count each batch separately</label>
                                    </div>
                                </div>
                                <div class="border rounded-4 p-3">
                                    <input type="hidden" name="count_each_course_separately" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="count_each_course_separately" name="count_each_course_separately" value="1" @checked(old('count_each_course_separately', $billing['count_each_course_separately'] ?? false))>
                                        <label class="form-check-label fw-semibold" for="count_each_course_separately">Count each course separately</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Save Billing Settings</button>
                </div>
            </form>
        @endif

        @if ($activeTab === 'communication')
            <form method="POST" action="{{ route('settings.communication.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-xl-5">
                        <div class="admin-card p-4">
                            <div class="page-section-title text-success-emphasis">Channels</div>
                            <div class="vstack gap-3 mt-2">
                                @foreach (['sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email'] as $key => $label)
                                    <div class="border rounded-4 p-3">
                                        <input type="hidden" name="channels[{{ $key }}]" value="0">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="channel_{{ $key }}" name="channels[{{ $key }}]" value="1" @checked(old("channels.$key", $communicationChannels[$key] ?? false))>
                                            <label class="form-check-label fw-semibold" for="channel_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <div class="admin-card p-4">
                            <div class="page-section-title text-primary-emphasis">Event Triggers</div>
                            <div class="row g-3 mt-1">
                                @foreach ([
                                    'admission' => 'Admission',
                                    'fee_payment' => 'Fee Payment',
                                    'due_reminder' => 'Due Reminder',
                                    'attendance_alert' => 'Attendance Alert',
                                    'exam_notice' => 'Exam Notice',
                                    'result_publish' => 'Result Publish',
                                ] as $key => $label)
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 h-100">
                                            <input type="hidden" name="events[{{ $key }}]" value="0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="event_{{ $key }}" name="events[{{ $key }}]" value="1" @checked(old("events.$key", $communicationEvents[$key] ?? false))>
                                                <label class="form-check-label fw-semibold" for="event_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Save Communication Settings</button>
                </div>
            </form>
        @endif

        @if ($activeTab === 'post-payment')
            <form method="POST" action="{{ route('settings.post-payment.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-xl-5">
                        <div class="admin-card p-4 mb-4">
                            <div class="page-section-title text-warning-emphasis">Action Controls</div>
                            <div class="vstack gap-3 mt-2">
                                <div class="border rounded-4 p-3">
                                    <input type="hidden" name="enabled" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" @checked(old('enabled', $postPayment['enabled'] ?? true))>
                                        <label class="form-check-label fw-semibold" for="enabled">Enable post-payment processing</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="admin-card p-4 mb-4">
                            <div class="page-section-title text-info-emphasis">Receipt Outputs</div>
                            <div class="vstack gap-3 mt-2">
                                @foreach (['printable' => 'Printable Receipt', 'normal_printer' => 'Normal Printer', 'pos_printer' => 'POS Printer'] as $key => $label)
                                    <div class="border rounded-4 p-3">
                                        <input type="hidden" name="receipts[{{ $key }}]" value="0">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="receipt_{{ $key }}" name="receipts[{{ $key }}]" value="1" @checked(old("receipts.$key", $postPayment['receipts'][$key] ?? false))>
                                            <label class="form-check-label fw-semibold" for="receipt_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                                <div>
                                    <label for="templates_receipt_default" class="form-label fw-semibold mt-2">Default Receipt Template</label>
                                    <select id="templates_receipt_default" name="templates[receipt_default]" class="form-select rounded-4">
                                        <option value="printable" @selected(old('templates.receipt_default', $postPayment['templates']['receipt_default'] ?? 'printable') === 'printable')>Printable</option>
                                        <option value="normal" @selected(old('templates.receipt_default', $postPayment['templates']['receipt_default'] ?? 'printable') === 'normal')>Normal</option>
                                        <option value="pos" @selected(old('templates.receipt_default', $postPayment['templates']['receipt_default'] ?? 'printable') === 'pos')>POS</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="admin-card p-4">
                            <div class="page-section-title text-success-emphasis">Notification Outputs</div>
                            <div class="vstack gap-3 mt-2">
                                @foreach (['sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email'] as $key => $label)
                                    <div class="border rounded-4 p-3">
                                        <input type="hidden" name="notifications[{{ $key }}]" value="0">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notification_{{ $key }}" name="notifications[{{ $key }}]" value="1" @checked(old("notifications.$key", $postPayment['notifications'][$key] ?? false))>
                                            <label class="form-check-label fw-semibold" for="notification_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7">
                        <div class="admin-card p-4">
                            <div class="page-section-title text-primary-emphasis">Templates</div>
                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label for="template_sms" class="form-label fw-semibold">SMS Template</label>
                                    <textarea id="template_sms" name="templates[sms]" rows="3" class="form-control rounded-4">{{ old('templates.sms', $postPayment['templates']['sms'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="template_whatsapp" class="form-label fw-semibold">WhatsApp Template</label>
                                    <textarea id="template_whatsapp" name="templates[whatsapp]" rows="3" class="form-control rounded-4">{{ old('templates.whatsapp', $postPayment['templates']['whatsapp'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="template_email_subject" class="form-label fw-semibold">Email Subject</label>
                                    <input id="template_email_subject" name="templates[email_subject]" type="text" class="form-control rounded-4" value="{{ old('templates.email_subject', $postPayment['templates']['email_subject'] ?? '') }}">
                                </div>
                                <div class="col-12">
                                    <label for="template_email_body" class="form-label fw-semibold">Email Body</label>
                                    <textarea id="template_email_body" name="templates[email_body]" rows="6" class="form-control rounded-4">{{ old('templates.email_body', $postPayment['templates']['email_body'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="small text-secondary">
                                        Available placeholders: <code>{{ '{' }}{receipt_no}{{ '}' }}</code>, <code>{{ '{' }}{student_name}{{ '}' }}</code>, <code>{{ '{' }}{paid_amount}{{ '}' }}</code>, <code>{{ '{' }}{due_amount}{{ '}' }}</code>, <code>{{ '{' }}{collector_name}{{ '}' }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Save Post-Payment Settings</button>
                </div>
            </form>
        @endif
    </div>
@endsection
