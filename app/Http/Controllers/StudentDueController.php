<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateStudentDueRequest;
use App\Models\Student;
use App\Models\StudentDue;
use App\Services\DueLedgerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDueController extends Controller
{
    public function __construct(
        protected DueLedgerService $dueLedgerService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentDue::class);

        $dues = StudentDue::query()
            ->with(['student', 'batch', 'ownerTeacher', 'feeHead', 'feeStructure'])
            ->visibleTo($request->user())
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->string('q'));

                $query->where(function (Builder $dueQuery) use ($search): void {
                    $dueQuery->where('billing_period_key', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('feeHead', fn (Builder $feeHeadQuery) => $feeHeadQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('batch', fn (Builder $batchQuery) => $batchQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('billing_period_key'), fn (Builder $query) => $query->where('billing_period_key', $request->string('billing_period_key')))
            ->when($request->filled('student_id'), fn (Builder $query) => $query->where('student_id', $request->integer('student_id')))
            ->latest('period_start')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = StudentDue::query()->visibleTo($request->user());

        return view('dues.index', [
            'dues' => $dues,
            'statuses' => StudentDue::statuses(),
            'students' => Student::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name', 'student_code']),
            'defaultPeriodKey' => $request->input('billing_period_key', now()->format('Y-m')),
            'summary' => [
                'charge' => (float) (clone $summaryQuery)->sum('charge_amount'),
                'paid' => (float) (clone $summaryQuery)->sum('paid_amount'),
                'due' => (float) (clone $summaryQuery)->sum('due_amount'),
            ],
        ]);
    }

    public function generate(GenerateStudentDueRequest $request): RedirectResponse
    {
        $this->authorize('generate', StudentDue::class);

        $periodKey = (string) $request->validated()['billing_period_key'];

        if ($request->filled('student_id')) {
            $student = Student::query()
                ->visibleTo($request->user())
                ->findOrFail($request->integer('student_id'));

            $generated = $this->dueLedgerService->generateMonthlyForStudent($student, $periodKey);

            return redirect()
                ->route('dues.index', ['billing_period_key' => $periodKey, 'student_id' => $student->getKey()])
                ->with('status', "{$generated->count()} due rows generated or refreshed for {$student->name}.");
        }

        $generated = $this->dueLedgerService->generateMonthlyForTenant($request->user()->tenant, $periodKey);

        return redirect()
            ->route('dues.index', ['billing_period_key' => $periodKey])
            ->with('status', "{$generated->count()} due rows generated or refreshed for {$periodKey}.");
    }

    public function show(StudentDue $due): View
    {
        $this->authorize('view', $due);

        return view('dues.show', [
            'due' => $due->load([
                'student',
                'batch',
                'ownerTeacher',
                'feeHead',
                'feeStructure',
                'paymentItems.payment.collector',
            ]),
        ]);
    }
}
