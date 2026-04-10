<div class="row g-4">
    <div class="col-12 col-xl-8">
        @if ($selectedStudent)
            <div class="admin-card p-4 mb-4">
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">

                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="small text-secondary">Student</div>
                        <div class="fw-semibold">{{ $selectedStudent->name }}</div>
                        <div class="small text-secondary">{{ $selectedStudent->student_code }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary">Owner Teacher</div>
                        <div class="fw-semibold">{{ $selectedStudent->ownerTeacher?->name ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary">Active Enrollment</div>
                        <div class="fw-semibold">{{ $selectedEnrollment?->batch?->name ?? 'No active enrollment selected' }}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="small text-secondary">Student context is locked from lookup for safer fee collection.</div>
                    <a href="{{ route('payments.create', ['billing_period_key' => request('billing_period_key', now()->format('Y-m'))]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Change Student</a>
                </div>
            </div>

            <div class="admin-card p-3 p-lg-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="page-section-title text-success-emphasis">Due Preview</div>
                        <div class="small text-secondary">Outstanding dues for the selected student and billing period.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle module-table mb-0">
                        <thead>
                            <tr>
                                <th>Fee Head</th>
                                <th>Structure</th>
                                <th>Charge</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dueRows as $row)
                                <tr class="{{ $row['due_amount'] > 0 ? 'payment-due-row' : 'payment-due-row is-settled' }}"
                                    data-fee-structure-id="{{ $row['fee_structure']->id }}"
                                    data-enrollment-id="{{ $row['student_due']->student_enrollment_id }}"
                                    data-batch-id="{{ $row['student_due']->batch_id }}"
                                    data-period-type="{{ $row['student_due']->billing_period_type }}"
                                    data-due-amount="{{ number_format($row['due_amount'], 2, '.', '') }}"
                                    data-charge-amount="{{ number_format($row['charge_amount'], 2, '.', '') }}">
                                    <td>{{ $row['fee_structure']->feeHead?->name }}</td>
                                    <td>{{ $row['fee_structure']->title }}</td>
                                    <td>{{ number_format($row['charge_amount'], 2) }}</td>
                                    <td>{{ number_format($row['paid_amount'], 2) }}</td>
                                    <td class="fw-semibold {{ $row['due_amount'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($row['due_amount'], 2) }}</td>
                                    <td class="text-end">
                                        @if ($row['due_amount'] > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 js-use-due">Use Full Due</button>
                                        @else
                                            <span class="badge rounded-pill text-bg-success">Settled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">No due preview rows are available for this student and period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="small text-secondary mt-3">Tip: click `Use Full Due` on any unpaid row to auto-fill the structure and payable amount in the form below.</div>
            </div>
        @endif

        <div class="admin-card p-4">
            <div class="row g-3">
                @if (! $selectedStudent)
                    <div class="col-md-6">
                        <label for="student_id" class="form-label fw-semibold">Student</label>
                        <select id="student_id" name="student_id" class="form-select rounded-4" required>
                            <option value="">Select student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->name }} ({{ $student->student_code }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">For accounting safety, prefer loading a student from the lookup box above before collecting.</div>
                        @error('student_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="col-md-6">
                    <label for="student_enrollment_id" class="form-label fw-semibold">Enrollment</label>
                    <select id="student_enrollment_id" name="student_enrollment_id" class="form-select rounded-4">
                        <option value="">No specific enrollment</option>
                        @foreach ($enrollments as $enrollment)
                            <option value="{{ $enrollment->id }}" data-batch-id="{{ $enrollment->batch_id }}" @selected((string) old('student_enrollment_id', $selectedEnrollment?->id) === (string) $enrollment->id)>
                                {{ $enrollment->batch?->name }} ({{ $enrollment->batch?->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_enrollment_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="batch_id" class="form-label fw-semibold">Batch</label>
                    <select id="batch_id" name="batch_id" class="form-select rounded-4">
                        <option value="">No direct batch override</option>
                        @foreach ($availableBatches as $batch)
                            <option value="{{ $batch->id }}"
                                data-owner-teacher="{{ $batch->ownerTeacher?->name ?? '' }}"
                                @selected((string) old('batch_id', $selectedEnrollment?->batch_id) === (string) $batch->id)>
                                {{ $batch->name }} ({{ $batch->code }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Batch choices are limited to the selected student's active enrollment batches.</div>
                    @error('batch_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="fee_structure_id" class="form-label fw-semibold">Fee Structure</label>
                    <select id="fee_structure_id" name="fee_structure_id" class="form-select rounded-4" required>
                        <option value="">Select structure</option>
                        @foreach ($feeStructures as $feeStructure)
                            <option value="{{ $feeStructure->id }}" @selected((string) old('fee_structure_id', request('fee_structure_id')) === (string) $feeStructure->id)>{{ $feeStructure->title }} ({{ $feeStructure->feeHead?->name }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Fee head is derived from the selected structure and stored automatically on the receipt item.</div>
                    @error('fee_structure_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="billing_period_type" class="form-label fw-semibold">Period Type</label>
                    <select id="billing_period_type" name="billing_period_type" class="form-select rounded-4">
                        @foreach ($periodTypes as $periodType)
                            <option value="{{ $periodType }}" @selected(old('billing_period_type', request('billing_period_type', \App\Models\PaymentItem::PERIOD_TYPE_MONTH)) === $periodType)>{{ str($periodType)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    @error('billing_period_type') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="billing_period_key" class="form-label fw-semibold">Billing Period Key</label>
                    <input id="billing_period_key" name="billing_period_key" type="text" class="form-control rounded-4" value="{{ old('billing_period_key', request('billing_period_key', now()->format('Y-m'))) }}" required>
                    <div class="form-text">Examples: `2026-04`, `admission-2026`, `exam-midterm-2026`</div>
                    @error('billing_period_key') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="paid_amount" class="form-label fw-semibold">Paid Amount</label>
                    <input id="paid_amount" name="paid_amount" type="number" min="0.01" step="0.01" class="form-control rounded-4" value="{{ old('paid_amount') }}" required>
                    @error('paid_amount') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="period_start" class="form-label fw-semibold">Period Start</label>
                    <input id="period_start" name="period_start" type="date" class="form-control rounded-4" value="{{ old('period_start', now()->startOfMonth()->format('Y-m-d')) }}">
                    @error('period_start') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="period_end" class="form-label fw-semibold">Period End</label>
                    <input id="period_end" name="period_end" type="date" class="form-control rounded-4" value="{{ old('period_end', now()->endOfMonth()->format('Y-m-d')) }}">
                    @error('period_end') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="payment_method" class="form-label fw-semibold">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select rounded-4">
                        @foreach ($methods as $method)
                            <option value="{{ $method }}" @selected(old('payment_method', $payment->payment_method) === $method)>{{ str($method)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    @error('payment_method') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="collected_on" class="form-label fw-semibold">Collected On</label>
                    <input id="collected_on" name="collected_on" type="datetime-local" class="form-control rounded-4" value="{{ old('collected_on', $payment->collected_on) }}" required>
                    @error('collected_on') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="payment_notes" class="form-label fw-semibold">Receipt Notes</label>
                    <textarea id="payment_notes" name="payment_notes" rows="3" class="form-control rounded-4">{{ old('payment_notes') }}</textarea>
                    @error('payment_notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="item_notes" class="form-label fw-semibold">Item Notes</label>
                    <textarea id="item_notes" name="item_notes" rows="3" class="form-control rounded-4">{{ old('item_notes') }}</textarea>
                    @error('item_notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="admin-card p-4 mb-4">
            <div class="page-section-title text-warning-emphasis">Collection Notes</div>
            <ul class="small text-secondary ps-3 mb-0">
                <li>Student should be locked from lookup before final collection whenever possible.</li>
                <li>Owner teacher is resolved from the student or batch account, not typed manually.</li>
                <li>Collector is always the logged-in user who received the payment.</li>
                <li>Fee head is derived from the chosen fee structure and cannot drift separately.</li>
            </ul>
        </div>

        <div class="admin-card p-4">
            <div class="page-section-title text-success-emphasis">Suggested Due</div>
            <div class="h3 fw-bold mb-2">{{ $suggestedDue !== null ? number_format($suggestedDue, 2) : 'Select student + structure + period' }}</div>
            <div class="small text-secondary">Partial payment is allowed. To take advance, use a future billing period key and dates.</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('payments.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Collect Fee</button>
</div>

@push('styles')
    <style>
        .payment-due-row {
            transition: background-color 0.15s ease, box-shadow 0.15s ease;
        }

        .payment-due-row:not(.is-settled) {
            cursor: pointer;
        }

        .payment-due-row:not(.is-settled):hover {
            background-color: rgba(13, 110, 253, 0.06);
        }

        .payment-due-row.is-selected {
            background-color: rgba(25, 135, 84, 0.10);
            box-shadow: inset 3px 0 0 #198754;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const feeStructureSelect = document.getElementById('fee_structure_id');
            const paidAmountInput = document.getElementById('paid_amount');
            const enrollmentSelect = document.getElementById('student_enrollment_id');
            const batchSelect = document.getElementById('batch_id');
            const periodTypeSelect = document.getElementById('billing_period_type');
            const dueRows = document.querySelectorAll('.payment-due-row');

            if (!feeStructureSelect || !paidAmountInput) {
                return;
            }

            if (enrollmentSelect && batchSelect) {
                const syncBatchFromEnrollment = () => {
                    const selectedOption = enrollmentSelect.options[enrollmentSelect.selectedIndex];

                    if (!selectedOption || !selectedOption.value) {
                        batchSelect.value = '';
                        return;
                    }

                    const batchId = selectedOption.dataset.batchId;

                    if (batchId) {
                        batchSelect.value = batchId;
                    }
                };

                syncBatchFromEnrollment();

                enrollmentSelect.addEventListener('change', syncBatchFromEnrollment);
            }

            if (dueRows.length === 0) {
                return;
            }

            const applyDueRow = (row) => {
                const feeStructureId = row.dataset.feeStructureId;
                const dueAmount = row.dataset.dueAmount;
                const enrollmentId = row.dataset.enrollmentId;
                const batchId = row.dataset.batchId;
                const periodType = row.dataset.periodType;

                if (!feeStructureId || !dueAmount || Number(dueAmount) <= 0) {
                    return;
                }

                feeStructureSelect.value = feeStructureId;
                paidAmountInput.value = dueAmount;

                if (periodTypeSelect && periodType) {
                    periodTypeSelect.value = periodType;
                }

                if (enrollmentSelect) {
                    enrollmentSelect.value = enrollmentId || '';
                }

                if (batchSelect) {
                    batchSelect.value = batchId || '';
                }

                paidAmountInput.focus();
                paidAmountInput.select();

                dueRows.forEach((currentRow) => currentRow.classList.remove('is-selected'));
                row.classList.add('is-selected');
            };

            dueRows.forEach((row) => {
                if (row.classList.contains('is-settled')) {
                    return;
                }

                row.addEventListener('click', function (event) {
                    if (event.target.closest('.js-use-due')) {
                        return;
                    }

                    applyDueRow(row);
                });

                const button = row.querySelector('.js-use-due');

                if (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        applyDueRow(row);
                    });
                }
            });
        });
    </script>
@endpush
