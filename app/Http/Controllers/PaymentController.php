<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Batch;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\DueLedgerService;
use App\Services\FeeCollectionService;
use App\Services\FeeStructureResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        protected FeeCollectionService $feeCollectionService,
        protected FeeStructureResolver $feeStructureResolver,
        protected DueLedgerService $dueLedgerService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with(['student', 'batch', 'ownerTeacher', 'collector', 'items.feeHead'])
            ->visibleTo($request->user())
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->string('q'));

                $query->where(function (Builder $paymentQuery) use ($search): void {
                    $paymentQuery
                        ->where('receipt_no', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('collector', function (Builder $collectorQuery) use ($search): void {
                            $collectorQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('payment_method'), fn (Builder $query) => $query->where('payment_method', $request->string('payment_method')))
            ->latest('collected_on')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = Payment::query()->visibleTo($request->user())->where('status', Payment::STATUS_RECEIVED);

        return view('payments.index', [
            'payments' => $payments,
            'methods' => Payment::methods(),
            'todayTotal' => (float) (clone $summaryQuery)->whereDate('collected_on', now()->toDateString())->sum('total_amount'),
            'monthTotal' => (float) (clone $summaryQuery)->whereBetween('collected_on', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Payment::class);

        $student = $request->filled('student_id')
            ? Student::query()->visibleTo($request->user())->find($request->integer('student_id'))
            : $this->lookupStudent($request);
        $enrollment = $request->filled('student_enrollment_id')
            ? StudentEnrollment::query()->visibleTo($request->user())->find($request->integer('student_enrollment_id'))
            : null;
        $selectedStudent = $student ?? $enrollment?->student;
        $selectedEnrollment = $enrollment ?? ($selectedStudent ? $this->enrollments($request->user(), $selectedStudent)->first() : null);

        return view('payments.create', [
            'payment' => new Payment([
                'payment_method' => Payment::METHOD_CASH,
                'collected_on' => now()->format('Y-m-d\TH:i'),
            ]),
            'students' => $this->students($request->user()),
            'enrollments' => $selectedStudent ? $this->enrollments($request->user(), $selectedStudent) : collect(),
            'availableBatches' => $selectedStudent ? $this->availableBatches($request->user(), $selectedStudent) : collect(),
            'feeStructures' => $selectedStudent ? $this->feeStructureResolver->forStudent($selectedStudent, $selectedEnrollment) : $this->feeStructures($request->user()),
            'selectedStudent' => $selectedStudent,
            'selectedEnrollment' => $selectedEnrollment,
            'methods' => Payment::methods(),
            'periodTypes' => PaymentItem::periodTypes(),
            'suggestedDue' => $this->suggestedDue($selectedStudent, $selectedEnrollment, $request),
            'dueRows' => $this->dueRows($selectedStudent, $selectedEnrollment, $request),
            'lookupValue' => $request->string('student_lookup')->toString(),
            'lookupMissed' => $request->filled('student_lookup') && ! $selectedStudent,
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $student = Student::query()->findOrFail($request->integer('student_id'));
        $enrollment = $request->filled('student_enrollment_id')
            ? StudentEnrollment::query()->findOrFail($request->integer('student_enrollment_id'))
            : null;
        $batch = $request->filled('batch_id')
            ? Batch::query()->findOrFail($request->integer('batch_id'))
            : null;
        $feeStructure = FeeStructure::query()->findOrFail($request->integer('fee_structure_id'));

        try {
            $payment = $this->feeCollectionService->collect(
                collector: $request->user(),
                student: $student,
                feeStructure: $feeStructure,
                paidAmount: (float) $request->validated()['paid_amount'],
                paymentMethod: $request->validated()['payment_method'],
                collectedOn: Carbon::parse($request->validated()['collected_on']),
                billingPeriodType: $request->validated()['billing_period_type'],
                billingPeriodKey: $request->validated()['billing_period_key'],
                periodStart: filled($request->validated()['period_start'] ?? null) ? Carbon::parse($request->validated()['period_start']) : null,
                periodEnd: filled($request->validated()['period_end'] ?? null) ? Carbon::parse($request->validated()['period_end']) : null,
                enrollment: $enrollment,
                batch: $batch,
                paymentNotes: $request->validated()['payment_notes'] ?? null,
                itemNotes: $request->validated()['item_notes'] ?? null,
            );
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['paid_amount' => $exception->getMessage()]);
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('status', 'Fee collected successfully.');
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('payments.show', [
            'payment' => $payment->load(['student', 'batch', 'ownerTeacher', 'collector', 'items.feeHead', 'items.feeStructure', 'postActions']),
        ]);
    }

    protected function students($user)
    {
        return Student::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->get(['id', 'name', 'student_code']);
    }

    protected function enrollments($user, Student $student)
    {
        return StudentEnrollment::query()
            ->visibleTo($user)
            ->where('student_id', $student->getKey())
            ->with('batch')
            ->where('status', StudentEnrollment::STATUS_ACTIVE)
            ->orderByDesc('enrolled_at')
            ->get();
    }

    protected function availableBatches($user, Student $student)
    {
        return $this->enrollments($user, $student)
            ->pluck('batch')
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function suggestedDue(?Student $student, ?StudentEnrollment $enrollment, Request $request): ?float
    {
        if (! $student || ! $request->filled('fee_structure_id') || ! $request->filled('billing_period_key')) {
            return null;
        }

        $feeStructure = FeeStructure::query()->find($request->integer('fee_structure_id'));

        if (! $feeStructure) {
            return null;
        }

        return (float) $this->dueLedgerService->ensureDue(
            student: $student,
            feeStructure: $feeStructure,
            billingPeriodKey: (string) $request->input('billing_period_key'),
            enrollment: $enrollment,
            batch: $enrollment?->batch,
            billingPeriodType: (string) ($request->input('billing_period_type') ?: PaymentItem::PERIOD_TYPE_MONTH),
            periodStart: $request->filled('period_start') ? Carbon::parse((string) $request->input('period_start')) : null,
            periodEnd: $request->filled('period_end') ? Carbon::parse((string) $request->input('period_end')) : null,
        )->due_amount;
    }

    protected function dueRows(?Student $student, ?StudentEnrollment $enrollment, Request $request)
    {
        if (! $student) {
            return collect();
        }

        $periodKey = (string) ($request->input('billing_period_key') ?: now()->format('Y-m'));
        return $this->dueLedgerService
            ->duesForStudent($student, $periodKey, $enrollment)
            ->map(fn ($due) => [
                'student_due' => $due,
                'fee_structure' => $due->feeStructure,
                'charge_amount' => (float) $due->charge_amount,
                'paid_amount' => (float) $due->paid_amount,
                'due_amount' => (float) $due->due_amount,
            ])
            ->filter(fn (array $row) => $row['charge_amount'] > 0)
            ->values();
    }

    protected function feeStructures($user)
    {
        return FeeStructure::query()
            ->forCurrentTenant()
            ->with('feeHead')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    protected function lookupStudent(Request $request): ?Student
    {
        $lookup = trim((string) $request->string('student_lookup'));

        if ($lookup === '') {
            return null;
        }

        return Student::query()
            ->visibleTo($request->user())
            ->where(function (Builder $query) use ($lookup): void {
                if (ctype_digit($lookup)) {
                    $query->orWhere('id', (int) $lookup);
                }

                $query->orWhere('student_code', $lookup)
                    ->orWhere('student_code', 'like', $lookup.'%')
                    ->orWhere('name', 'like', '%'.$lookup.'%');
            })
            ->orderBy('student_code')
            ->first();
    }
}
