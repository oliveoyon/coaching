@extends('layouts.admin')

@section('title', 'Collect Payment')
@section('page-title', 'Collect Payment')
@section('page-subtitle', 'Simple payment collection')

@section('content')
    @php
        $studentFeeCount = $collectionRows->sum(fn ($group) => $group['fees']->count());
        $studentDueTotal = $collectionRows->sum(fn ($group) => $group['fees']->sum(fn ($feeRow) => $feeRow['summary']['remaining']));
        $openGroup = null;

        if ($errors->any()) {
            foreach ($collectionRows as $groupIndex => $group) {
                foreach ($group['fees'] as $feeIndex => $feeRow) {
                    $rowKey = $groupIndex.'_'.$feeIndex;

                    if ($errors->has("items.{$rowKey}.amount")) {
                        $openGroup = 'payment-group-'.$groupIndex;
                        break 2;
                    }
                }
            }
        }
    @endphp

    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.payments.create') }}" class="row g-3">
                <div class="col-lg-5">
                    <label for="student_search" class="form-label">Student</label>
                    <input type="text" name="student_search" id="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Code, name, phone, guardian phone">
                </div>

                <div class="col-lg-3">
                    <label for="batch" class="form-label">Batch</label>
                    <select name="batch" id="batch" class="form-select">
                        <option value="">All Batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) $selectedBatchId === (string) $batch->id)>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" name="month" id="month" value="{{ $selectedMonth }}" class="form-control">
                </div>

                <div class="col-sm-6 col-lg-1 d-grid">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <button type="submit" class="btn btn-outline-primary">Find</button>
                </div>

                <div class="col-sm-6 col-lg-1 d-grid">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <a href="{{ route('admin.payments.create') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @if (! $student)
            <div class="col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 mb-0">Students</h2>
                            <span class="small text-muted">{{ $students->count() }}</span>
                        </div>

                        @if ($students->isNotEmpty())
                            <div class="list-group list-group-flush">
                                @foreach ($students as $match)
                                    <a
                                        href="{{ route('admin.payments.create', ['student' => $match->id, 'student_search' => $studentSearch, 'batch' => $selectedBatchId, 'month' => $selectedMonth]) }}"
                                        class="list-group-item list-group-item-action rounded-4 mb-2 border {{ $student?->id === $match->id ? 'active border-primary' : '' }}"
                                    >
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($match->photoUrl())
                                                <img src="{{ $match->photoUrl() }}" alt="{{ $match->name }}" class="rounded-circle border" style="width: 46px; height: 46px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width: 46px; height: 46px;">No</div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="fw-semibold">{{ $match->name }}</div>
                                                <div class="small text-muted">{{ $match->student_code }}</div>
                                                <div class="small text-muted">{{ $match->phone ?: $match->guardian_phone ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="py-4 text-center">
                                <div class="fw-semibold mb-2">Find a student</div>
                                <div class="text-muted">Use search or filters above.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ $student ? 'col-12' : 'col-xl-8' }}">
            <div class="card page-card">
                <div class="card-body p-4">
                    @if ($student)
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                @if ($student->photoUrl())
                                    <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="rounded-circle border shadow-sm" style="width: 64px; height: 64px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width: 64px; height: 64px;">No</div>
                                @endif
                                <div>
                                    <h2 class="h4 mb-1">{{ $student->name }}</h2>
                                    <div class="text-muted small">{{ $student->student_code }} | {{ $student->phone ?: '-' }} | {{ $student->guardian_phone ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('admin.payments.create', ['student_search' => $studentSearch, 'batch' => $selectedBatchId, 'month' => $selectedMonth]) }}" class="btn btn-outline-secondary">Change Student</a>
                                <a href="{{ route('admin.student-profiles.show', $student) }}" class="btn btn-outline-secondary">Profile</a>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 bg-primary-subtle h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">Active Batches</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ $collectionRows->count() }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-info-subtle h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">Fee Lines</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ $studentFeeCount }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-danger-subtle h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">Total Due</div>
                                        <div class="fs-3 fw-semibold mt-2">{{ number_format($studentDueTotal, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($collectionRows->isNotEmpty())
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="h5 mb-1">Collect Fees</h3>
                                    <div class="small text-muted">Showing all unpaid items up to {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}.</div>
                                </div>
                            </div>

                            <div class="accordion" id="paymentBatchAccordion">
                                @foreach ($collectionRows as $groupIndex => $group)
                                    @php
                                        $groupDue = $group['fees']->sum(fn ($feeRow) => $feeRow['summary']['remaining']);
                                        $accordionId = 'payment-group-'.$groupIndex;
                                        $isOpen = $openGroup === $accordionId;
                                    @endphp
                                    <div class="accordion-item border rounded-4 overflow-hidden mb-3">
                                        <h2 class="accordion-header" id="heading-{{ $accordionId }}">
                                            <button class="accordion-button {{ $isOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accordionId }}" aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="{{ $accordionId }}">
                                                <div class="w-100 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 pe-3">
                                                    <div>
                                                        <div class="fw-semibold">{{ $group['enrollment']->batch?->name }}</div>
                                                        <div class="small text-muted">
                                                            {{ $group['enrollment']->batch?->academicClass?->name }}
                                                            @if ($group['enrollment']->batch?->subject)
                                                                | {{ $group['enrollment']->batch?->subject?->name }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="text-lg-end">
                                                        <div class="small text-muted">{{ $group['fees']->count() }} fee line(s)</div>
                                                        <div class="fw-semibold text-danger">{{ number_format($groupDue, 2) }}</div>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="{{ $accordionId }}" class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}" aria-labelledby="heading-{{ $accordionId }}" data-bs-parent="#paymentBatchAccordion">
                                            <div class="accordion-body">
                                                <form method="POST" action="{{ route('admin.payments.store') }}" class="payment-batch-form">
                                                    @csrf
                                                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                                                    <div class="border rounded-4 p-4 mb-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label for="payment_date_{{ $accordionId }}" class="form-label">Payment Date</label>
                                                                <input type="date" name="payment_date" id="payment_date_{{ $accordionId }}" value="{{ old('payment_date', now()->format('Y-m-d')) }}" class="form-control @error('payment_date') is-invalid @enderror" required>
                                                                @error('payment_date')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label for="method_{{ $accordionId }}" class="form-label">Method</label>
                                                                <select name="method" id="method_{{ $accordionId }}" class="form-select payment-method-select @error('method') is-invalid @enderror" required>
                                                                    <option value="cash" @selected(old('method', 'cash') === 'cash')>Cash</option>
                                                                    <option value="bkash" @selected(old('method') === 'bkash')>bKash</option>
                                                                    <option value="nagad" @selected(old('method') === 'nagad')>Nagad</option>
                                                                </select>
                                                                @error('method')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-12 payment-transaction-block">
                                                                <label for="transaction_id_{{ $accordionId }}" class="form-label">Transaction ID</label>
                                                                <input type="text" name="transaction_id" id="transaction_id_{{ $accordionId }}" value="{{ old('transaction_id') }}" class="form-control @error('transaction_id') is-invalid @enderror">
                                                                @error('transaction_id')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" data-batch-fill="{{ $accordionId }}">Fill Due</button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-batch-clear="{{ $accordionId }}">Clear</button>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Fee</th>
                                                                    <th>Month</th>
                                                                    <th>Type</th>
                                                                    <th class="text-end">Payable</th>
                                                                    <th class="text-end">Approved</th>
                                                                    <th class="text-end">Pending</th>
                                                                    <th class="text-end">Due</th>
                                                                    <th style="min-width: 170px;">Collect Now</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($group['fees'] as $feeIndex => $feeRow)
                                                                    @php($rowKey = $groupIndex.'_'.$feeIndex)
                                                                    @php($oldAmount = old("items.{$rowKey}.amount"))
                                                                    <tr data-batch-group="{{ $accordionId }}">
                                                                        <td>
                                                                            <div class="fw-semibold">{{ $feeRow['batch_fee']->feeType?->name }}</div>
                                                                            <input type="hidden" name="items[{{ $rowKey }}][enrollment_id]" value="{{ $group['enrollment']->id }}">
                                                                            <input type="hidden" name="items[{{ $rowKey }}][batch_fee_id]" value="{{ $feeRow['batch_fee']->id }}">
                                                                            <input type="hidden" name="items[{{ $rowKey }}][month]" value="{{ $feeRow['billing_month'] }}">
                                                                        </td>
                                                                        <td>
                                                                            @if ($feeRow['billing_month'])
                                                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $feeRow['billing_month'])->format('M Y') }}
                                                                            @else
                                                                                <span class="text-muted">One time</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ ucfirst(str_replace('_', ' ', $feeRow['batch_fee']->feeType?->frequency ?? '')) }}</td>
                                                                        <td class="text-end">
                                                                            {{ number_format($feeRow['summary']['fee'], 2) }}
                                                                            @if (($feeRow['summary']['discount'] ?? 0) > 0)
                                                                                <div class="small text-muted">
                                                                                    Base {{ number_format($feeRow['summary']['base_fee'], 2) }} | Less {{ number_format($feeRow['summary']['discount'], 2) }}
                                                                                </div>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-end">{{ number_format($feeRow['summary']['approved'], 2) }}</td>
                                                                        <td class="text-end">{{ number_format($feeRow['summary']['pending'], 2) }}</td>
                                                                        <td class="text-end fw-semibold {{ $feeRow['summary']['remaining'] > 0 ? 'text-danger' : 'text-success' }}">
                                                                            {{ number_format($feeRow['summary']['remaining'], 2) }}
                                                                        </td>
                                                                        <td>
                                                                            <input
                                                                                type="number"
                                                                                step="0.01"
                                                                                name="items[{{ $rowKey }}][amount]"
                                                                                value="{{ $oldAmount !== null ? $oldAmount : '' }}"
                                                                                placeholder="0.00"
                                                                                class="form-control payment-amount-input @error("items.{$rowKey}.amount") is-invalid @enderror"
                                                                                data-default-amount="{{ number_format($feeRow['summary']['remaining'], 2, '.', '') }}"
                                                                                data-batch-group="{{ $accordionId }}"
                                                                            >
                                                                            @error("items.{$rowKey}.amount")
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                                        <button type="submit" class="btn btn-primary">Save This Batch</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-5 text-center text-muted">No fee found for this student with the current filter.</div>
                        @endif
                    @else
                        <div class="py-5 text-center">
                            <div class="fw-semibold mb-2">Select a student</div>
                            <div class="text-muted">Use the filters above to open the student here.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const syncMethodView = (form) => {
                const methodField = form.querySelector('.payment-method-select');
                const transactionBlock = form.querySelector('.payment-transaction-block');

                if (!methodField || !transactionBlock) {
                    return;
                }

                transactionBlock.style.display = methodField.value === 'cash' ? 'none' : '';
            };

            const amountInputs = Array.from(document.querySelectorAll('.payment-amount-input'));

            const setInputsByGroup = (groupName, mode) => {
                amountInputs
                    .filter((input) => input.dataset.batchGroup === groupName)
                    .forEach((input) => {
                        input.value = mode === 'fill' ? input.dataset.defaultAmount : '';
                    });
            };

            document.querySelectorAll('[data-batch-fill]').forEach((button) => {
                button.addEventListener('click', () => {
                    setInputsByGroup(button.dataset.batchFill, 'fill');
                });
            });

            document.querySelectorAll('[data-batch-clear]').forEach((button) => {
                button.addEventListener('click', () => {
                    setInputsByGroup(button.dataset.batchClear, 'clear');
                });
            });

            document.querySelectorAll('.payment-batch-form').forEach((form) => {
                const methodField = form.querySelector('.payment-method-select');

                methodField?.addEventListener('change', () => syncMethodView(form));
                syncMethodView(form);
            });
        })();
    </script>
@endpush
