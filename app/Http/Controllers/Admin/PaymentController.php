<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentStatusRequest;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\Batch;
use App\Models\BatchFee;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Services\IncomeDistributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display payment history.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $month = (string) $request->string('month');
        $status = (string) $request->string('status');
        $method = (string) $request->string('method');
        $activeTab = (string) $request->string('tab');
        $activeTab = in_array($activeTab, ['collect', 'pending', 'due', 'history'], true) ? $activeTab : 'collect';
        if ($activeTab === 'pending' && ! $request->user()->can('approve payments')) {
            $activeTab = 'collect';
        }
        $dashboardMonth = $month !== '' ? $month : now()->format('Y-m');
        $today = now()->toDateString();
        $monthStart = $dashboardMonth.'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $payments = Payment::query()
            ->with([
                'enrollment.student.academicClass',
                'enrollment.batch.academicClass',
                'enrollment.batch.subject',
                'batchFee.feeType',
                'collector',
                'approver',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('enrollment.student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('guardian_phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('enrollment.batch', fn ($batchQuery) => $batchQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($month !== '', fn ($query) => $query->where('month', $month))
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->when(in_array($method, ['cash', 'bkash', 'nagad'], true), fn ($query) => $query->where('method', $method))
            ->when($request->user()->hasRole('Teacher'), fn ($query) => $this->scopePaymentsToTeacher($query, $request))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summaryCards = [
            'today_collected' => (float) $this->paymentsScopeForUser($request)
                ->whereDate('payment_date', $today)
                ->where('status', 'approved')
                ->sum('amount'),
            'pending_count' => (clone $this->paymentsScopeForUser($request))
                ->where('status', 'pending')
                ->count(),
            'month_approved' => (float) (clone $this->paymentsScopeForUser($request))
                ->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->where('status', 'approved')
                ->sum('amount'),
        ];

        $pendingPayments = $request->user()->can('approve payments')
            ? (clone $this->paymentsScopeForUser($request))
            ->with(['enrollment.student', 'enrollment.batch', 'batchFee.feeType'])
            ->where('status', 'pending')
            ->latest('payment_date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->groupBy(fn ($payment) => $payment->payment_date?->format('d M Y') ?: 'Unknown Date')
            : collect();

        $dueGroups = $this->eligibleEnrollmentsQuery($request)
            ->with([
                'student',
                'batch.academicClass',
                'batch.subject',
                'batch.batchFees.feeType',
                'payments.batchFee.feeType',
            ])
            ->whereDate('start_date', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $monthStart);
            })
            ->get()
            ->flatMap(function (Enrollment $enrollment) use ($dashboardMonth) {
                return $enrollment->batch?->batchFees?->where('status', 'active')
                    ->map(function (BatchFee $batchFee) use ($enrollment, $dashboardMonth) {
                        $isMonthly = $batchFee->feeType?->frequency === 'monthly';
                        $summary = $this->feeSummary($enrollment, $batchFee, $isMonthly ? $dashboardMonth : null);

                        if ($summary['remaining'] <= 0) {
                            return null;
                        }

                        return [
                            'batch_id' => $enrollment->batch_id,
                            'batch_name' => $enrollment->batch?->name,
                            'class_name' => $enrollment->batch?->academicClass?->name,
                            'student_id' => $enrollment->student_id,
                            'fee_item' => $batchFee->feeType?->name,
                            'remaining' => $summary['remaining'],
                        ];
                    })
                    ->filter() ?? collect();
            })
            ->groupBy('batch_id')
            ->map(function ($items) {
                return [
                    'batch_name' => $items->first()['batch_name'] ?? '-',
                    'class_name' => $items->first()['class_name'] ?? '-',
                    'student_count' => $items->pluck('student_id')->unique()->count(),
                    'fee_item_count' => $items->count(),
                    'due_total' => (float) $items->sum('remaining'),
                    'top_fee_items' => $items->groupBy('fee_item')
                        ->map(fn ($group) => (float) $group->sum('remaining'))
                        ->sortDesc()
                        ->take(3),
                ];
            })
            ->sortByDesc('due_total')
            ->take(8)
            ->values();

        $summaryCards['due_student_count'] = $dueGroups->sum('student_count');

        return view('admin.payments.index', compact(
            'payments',
            'search',
            'month',
            'status',
            'method',
            'activeTab',
            'dashboardMonth',
            'summaryCards',
            'pendingPayments',
            'dueGroups',
        ));
    }

    /**
     * Show the form for collecting a payment.
     */
    public function create(Request $request): View
    {
        $selectedBatchId = $request->integer('batch');
        $studentSearch = trim((string) $request->string('student_search'));
        $selectedMonth = (string) $request->string('month') ?: now()->format('Y-m');
        $student = null;
        $students = collect();
        $studentQuery = Student::query()
            ->with(['academicClass'])
            ->where('status', 'active')
            ->whereHas('enrollments', function ($query) use ($selectedBatchId) {
                $query->where('status', 'active')
                    ->whereHas('batch', function ($batchQuery) use ($selectedBatchId) {
                        $batchQuery->where('status', 'active');

                        if ($selectedBatchId) {
                            $batchQuery->where('id', $selectedBatchId);
                        }
                    });
            });

        if ($request->user()->hasRole('Teacher')) {
            $teacher = $request->user()->teacherProfile;

            abort_if(! $teacher, Response::HTTP_FORBIDDEN);

            $studentQuery->whereHas('enrollments.batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
        }

        if ($studentSearch !== '') {
            $studentQuery->where(function ($query) use ($studentSearch, $selectedBatchId) {
                $query->where('student_code', 'like', "%{$studentSearch}%")
                    ->orWhere('name', 'like', "%{$studentSearch}%")
                    ->orWhere('phone', 'like', "%{$studentSearch}%")
                    ->orWhere('guardian_phone', 'like', "%{$studentSearch}%")
                    ->orWhereHas('enrollments.batch', function ($batchQuery) use ($studentSearch, $selectedBatchId) {
                        $batchQuery->where('name', 'like', "%{$studentSearch}%");

                        if ($selectedBatchId) {
                            $batchQuery->where('id', $selectedBatchId);
                        }
                    });
            });
        }

        if ($studentSearch !== '' || $selectedBatchId) {
            $students = $studentQuery
                ->orderBy('name')
                ->limit(30)
                ->get();
        }

        if ($request->filled('student')) {
            $student = Student::query()
                ->with([
                    'academicClass',
                    'enrollments' => function ($query) use ($selectedBatchId, $request) {
                        $query->where('status', 'active')
                            ->with([
                                'batch.academicClass',
                                'batch.subject',
                                'batch.batchFees.feeType',
                                'payments.batchFee.feeType',
                            ]);

                        if ($selectedBatchId) {
                            $query->where('batch_id', $selectedBatchId);
                        }

                        if ($request->user()->hasRole('Teacher')) {
                            $teacher = $request->user()->teacherProfile;

                            if ($teacher) {
                                $query->whereHas('batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
                            }
                        }
                    },
                ])
                ->where('status', 'active')
                ->findOrFail($request->integer('student'));
        }

        $collectionRows = $student
            ? $this->buildCollectionRows($student, $selectedMonth)
            : collect();

        return view('admin.payments.create', [
            'student' => $student,
            'selectedMonth' => $selectedMonth,
            'selectedBatchId' => $selectedBatchId,
            'studentSearch' => $studentSearch,
            'students' => $students,
            'collectionRows' => $collectionRows,
            'batches' => Batch::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(StorePaymentRequest $request, IncomeDistributionService $distributionService): RedirectResponse
    {
        $method = $request->string('method')->toString();
        $isCash = $method === 'cash';
        $month = $request->string('month')->toString() ?: null;
        $items = collect($request->validated('items'))
            ->filter(fn ($item) => (float) ($item['amount'] ?? 0) > 0);

        $batchFees = BatchFee::query()
            ->with('feeType')
            ->whereIn('id', $items->pluck('batch_fee_id')->unique())
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $batchFee = $batchFees->get((int) $item['batch_fee_id']);

            $payment = Payment::create([
                'enrollment_id' => $item['enrollment_id'],
                'batch_fee_id' => $item['batch_fee_id'],
                'amount' => $item['amount'],
                'month' => $batchFee?->feeType?->frequency === 'monthly' ? $month : null,
                'payment_date' => $request->date('payment_date')->format('Y-m-d'),
                'method' => $method,
                'transaction_id' => $request->string('transaction_id')->toString() ?: null,
                'status' => $isCash ? 'approved' : 'pending',
                'collected_by' => $request->user()->id,
                'approved_by' => $isCash ? $request->user()->id : null,
            ]);

            if ($isCash) {
                $distributionService->distribute($payment);
            }
        }

        return redirect()
            ->route('admin.student-profiles.show', $request->integer('student_id'))
            ->with('success', $isCash ? 'Payments collected and approved successfully.' : 'Payments submitted and are waiting for approval.');
    }

    /**
     * Approve a pending payment.
     */
    public function approve(PaymentStatusRequest $request, Payment $payment, IncomeDistributionService $distributionService): RedirectResponse
    {
        abort_if($payment->status !== 'pending', Response::HTTP_UNPROCESSABLE_ENTITY);

        $payment->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        $distributionService->distribute($payment->fresh());

        return redirect()
            ->route('admin.payments.index', ['tab' => $request->string('tab')->toString() ?: 'pending'])
            ->with('success', 'Payment approved successfully.');
    }

    /**
     * Reject a pending payment.
     */
    public function reject(PaymentStatusRequest $request, Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', Response::HTTP_UNPROCESSABLE_ENTITY);

        $payment->update([
            'status' => 'rejected',
            'approved_by' => null,
        ]);

        return redirect()
            ->route('admin.payments.index', ['tab' => $request->string('tab')->toString() ?: 'pending'])
            ->with('success', 'Payment rejected successfully.');
    }

    /**
     * Display due enrollments for a month.
     */
    public function dueList(Request $request): View
    {
        $month = (string) $request->string('month') ?: now()->format('Y-m');
        $search = trim((string) $request->string('search'));
        $periodStart = $month.'-01';
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        $dueEnrollments = $this->eligibleEnrollmentsQuery($request)
            ->with([
                'student.academicClass',
                'batch.academicClass',
                'batch.subject',
                'batch.batchFees.feeType',
                'payments.batchFee.feeType',
            ])
            ->whereDate('start_date', '<=', $periodEnd)
            ->where(function ($query) use ($periodStart) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $periodStart);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->whereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('guardian_phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy(Student::select('name')->whereColumn('students.id', 'enrollments.student_id'))
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.due-list', compact('dueEnrollments', 'month', 'search'));
    }

    /**
     * Build payment summary for a billing month.
     */
    public function feeSummary(Enrollment $enrollment, BatchFee $batchFee, ?string $month = null): array
    {
        $approved = $enrollment->approvedPaidForFee($batchFee, $month);
        $pending = $enrollment->pendingPaidForFee($batchFee, $month);
        $fee = (float) $batchFee->amount;

        return [
            'fee' => $fee,
            'approved' => $approved,
            'pending' => $pending,
            'remaining' => max(0, $fee - ($approved + $pending)),
        ];
    }

    /**
     * Build the list of enrollments that can participate in payment collection.
     */
    protected function eligibleEnrollmentsQuery(?Request $request = null)
    {
        return Enrollment::query()
            ->where('status', 'active')
            ->whereHas('student', fn ($query) => $query->where('status', 'active'))
            ->whereHas('batch', fn ($query) => $query->where('status', 'active'))
            ->when($request?->user()?->hasRole('Teacher'), fn ($query) => $this->scopeEnrollmentsToTeacher($query, $request));
    }

    /**
     * Build student payment rows with current due summary.
     */
    protected function buildCollectionRows(Student $student, string $month)
    {
        return $student->enrollments
            ->sortByDesc('id')
            ->map(function (Enrollment $enrollment) use ($month) {
                $feeRows = $enrollment->batch?->batchFees
                    ?->where('status', 'active')
                    ->map(function (BatchFee $batchFee) use ($enrollment, $month) {
                        $isMonthly = $batchFee->feeType?->frequency === 'monthly';
                        $summary = $this->feeSummary($enrollment, $batchFee, $isMonthly ? $month : null);

                        return [
                            'batch_fee' => $batchFee,
                            'is_monthly' => $isMonthly,
                            'summary' => $summary,
                        ];
                    })
                    ->filter(fn ($row) => $row['summary']['remaining'] > 0 || $row['summary']['approved'] > 0 || $row['summary']['pending'] > 0)
                    ->values() ?? collect();

                return [
                    'enrollment' => $enrollment,
                    'fees' => $feeRows,
                ];
            })
            ->filter(fn ($row) => $row['fees']->isNotEmpty())
            ->values();
    }

    /**
     * Build a payment query already scoped to the current user's batch access.
     */
    protected function paymentsScopeForUser(Request $request)
    {
        return Payment::query()
            ->when($request->user()->hasRole('Teacher'), fn ($query) => $this->scopePaymentsToTeacher($query, $request));
    }

    /**
     * Restrict payment query to the current teacher's assigned batches.
     */
    protected function scopePaymentsToTeacher($query, Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        abort_if(! $teacher, Response::HTTP_FORBIDDEN);

        return $query->whereHas('enrollment.batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
    }

    /**
     * Restrict enrollment query to the current teacher's assigned batches.
     */
    protected function scopeEnrollmentsToTeacher($query, Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        abort_if(! $teacher, Response::HTTP_FORBIDDEN);

        return $query->whereHas('batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
    }
}
