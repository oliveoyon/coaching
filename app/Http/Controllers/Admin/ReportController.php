<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Distribution;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherSettlement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the report hub.
     */
    public function index(Request $request): View
    {
        $this->authorizeReportAccess($request);

        return view('admin.reports.index', [
            'canViewAcademicReports' => $this->canViewAcademicReports($request),
            'canViewFinanceReports' => $this->canViewFinanceReports($request),
        ]);
    }

    /**
     * Display detailed student report.
     */
    public function students(Request $request): View
    {
        $this->authorizeAcademicReportAccess($request);

        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $filters = $this->baseFilters($request, includeTeachers: true);
        $teacherFilterId = $this->selectedTeacherId($request, $filters['teacherScopeId']);

        $students = Student::query()
            ->with([
                'academicClass',
                'enrollments' => function ($query) use ($filters, $teacherFilterId): void {
                    $query->where('status', 'active')
                        ->when($filters['batchId'], fn (Builder $enrollmentQuery, int $batchId) => $enrollmentQuery->where('batch_id', $batchId))
                        ->when($filters['accessibleBatchIds'] !== null, fn (Builder $enrollmentQuery) => $enrollmentQuery->whereIn('batch_id', $filters['accessibleBatchIds']))
                        ->when($teacherFilterId, fn (Builder $enrollmentQuery, int $teacherId) => $enrollmentQuery->whereHas('batch.teachers', fn (Builder $teacherQuery) => $teacherQuery->where('teachers.id', $teacherId)))
                        ->with(['batch.academicClass', 'batch.subject', 'batch.teachers.user'])
                        ->latest('start_date');
                },
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('students.student_code', 'like', "%{$search}%")
                        ->orWhere('students.name', 'like', "%{$search}%")
                        ->orWhere('students.phone', 'like', "%{$search}%")
                        ->orWhere('students.guardian_phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), fn (Builder $query) => $query->where('students.status', $status))
            ->when($filters['classId'], fn (Builder $query, int $classId) => $query->where('students.class_id', $classId))
            ->when($filters['batchId'], fn (Builder $query, int $batchId) => $query->whereHas('enrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('batch_id', $batchId)))
            ->when($teacherFilterId, fn (Builder $query, int $teacherId) => $query->whereHas('enrollments.batch.teachers', fn (Builder $teacherQuery) => $teacherQuery->where('teachers.id', $teacherId)))
            ->when($filters['accessibleBatchIds'] !== null, function (Builder $query) use ($filters): void {
                $query->whereHas('enrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery->whereIn('batch_id', $filters['accessibleBatchIds']));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.students', [
            'students' => $students,
            'search' => $search,
            'status' => $status,
            'selectedTeacherId' => $teacherFilterId,
        ] + $filters);
    }

    /**
     * Display due students report.
     */
    public function dues(Request $request): View
    {
        $this->authorizeFinanceReportAccess($request);

        $search = trim((string) $request->string('search'));
        $filters = $this->baseFilters($request, includeTeachers: true);
        $teacherFilterId = $this->selectedTeacherId($request, $filters['teacherScopeId']);
        $monthStart = $filters['month'].'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $dueRows = $this->enrollmentBaseQuery($filters['accessibleBatchIds'], $filters['classId'], $filters['batchId'], $teacherFilterId)
            ->where('enrollments.status', 'active')
            ->whereDate('enrollments.start_date', '<=', $monthEnd)
            ->where(function (Builder $query) use ($monthStart): void {
                $query->whereNull('enrollments.end_date')
                    ->orWhereDate('enrollments.end_date', '>=', $monthStart);
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($search): void {
                    $studentQuery->where(function (Builder $subQuery) use ($search): void {
                        $subQuery
                            ->where('students.student_code', 'like', "%{$search}%")
                            ->orWhere('students.name', 'like', "%{$search}%")
                            ->orWhere('students.phone', 'like', "%{$search}%")
                            ->orWhere('students.guardian_phone', 'like', "%{$search}%");
                    });
                });
            })
            ->with([
                'student.academicClass',
                'batch.academicClass',
                'batch.subject',
                'batch.batchFees.feeType',
                'payments.batchFee.feeType',
            ])
            ->get()
            ->flatMap(function (Enrollment $enrollment) use ($filters): Collection {
                return $enrollment->batch?->batchFees?->where('status', 'active')
                    ->map(function ($batchFee) use ($enrollment, $filters) {
                        $billingMonth = $batchFee->feeType?->frequency === 'monthly' ? $filters['month'] : null;
                        $remaining = $enrollment->remainingForFee($batchFee, $billingMonth);

                        if ($remaining <= 0) {
                            return null;
                        }

                        return [
                            'student_name' => $enrollment->student?->name,
                            'student_code' => $enrollment->student?->student_code,
                            'student_phone' => $enrollment->student?->phone,
                            'guardian_phone' => $enrollment->student?->guardian_phone,
                            'class_name' => $enrollment->batch?->academicClass?->name,
                            'batch_name' => $enrollment->batch?->name,
                            'fee_item' => $batchFee->feeType?->name,
                            'fee_frequency' => $batchFee->feeType?->frequency,
                            'remaining' => $remaining,
                        ];
                    })
                    ->filter() ?? collect();
            })
            ->sortBy([
                ['class_name', 'asc'],
                ['batch_name', 'asc'],
                ['student_name', 'asc'],
            ])
            ->values();

        return view('admin.reports.dues', [
            'dueRows' => $dueRows,
            'search' => $search,
            'selectedTeacherId' => $teacherFilterId,
        ] + $filters);
    }

    /**
     * Display collection reports.
     */
    public function collections(Request $request): View
    {
        $this->authorizeFinanceReportAccess($request);

        $filters = $this->baseFilters($request, includeTeachers: true);
        $teacherFilterId = $this->selectedTeacherId($request, $filters['teacherScopeId']);
        $monthStart = $filters['month'].'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $monthlyCollection = $this->approvedPaymentsSummaryQuery($filters['accessibleBatchIds'], $filters['classId'], $filters['batchId'], $teacherFilterId)
            ->whereBetween('payments.payment_date', [$monthStart, $monthEnd])
            ->selectRaw('payments.payment_date as report_date, COUNT(payments.id) as payment_count, SUM(payments.amount) as total_amount')
            ->groupBy('payments.payment_date')
            ->orderBy('payments.payment_date')
            ->get();

        $batchWiseCollection = $this->approvedPaymentsSummaryQuery($filters['accessibleBatchIds'], $filters['classId'], $filters['batchId'], $teacherFilterId)
            ->whereBetween('payments.payment_date', [$monthStart, $monthEnd])
            ->selectRaw('batches.id as batch_id, batches.name as batch_name, classes.name as class_name, COUNT(payments.id) as payment_count, SUM(payments.amount) as total_amount')
            ->groupBy('batches.id', 'batches.name', 'classes.name')
            ->orderByDesc('total_amount')
            ->get();

        $teacherWiseCollection = $this->approvedPaymentsSummaryQuery($filters['accessibleBatchIds'], $filters['classId'], $filters['batchId'], $teacherFilterId)
            ->join('batch_teacher', 'batch_teacher.batch_id', '=', 'batches.id')
            ->join('teachers', 'teachers.id', '=', 'batch_teacher.teacher_id')
            ->join('users as teacher_users', 'teacher_users.id', '=', 'teachers.user_id')
            ->selectRaw('teachers.id as teacher_id, teacher_users.name as teacher_name, COUNT(payments.id) as payment_count, SUM(payments.amount) as total_amount')
            ->whereBetween('payments.payment_date', [$monthStart, $monthEnd])
            ->groupBy('teachers.id', 'teacher_users.name')
            ->orderByDesc('total_amount')
            ->get();

        $paymentHistory = $this->approvedPaymentsDetailQuery($filters['accessibleBatchIds'], $filters['classId'], $filters['batchId'], $teacherFilterId)
            ->whereBetween('payments.payment_date', [$monthStart, $monthEnd])
            ->latest('payments.payment_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.collections', [
            'monthlyCollection' => $monthlyCollection,
            'batchWiseCollection' => $batchWiseCollection,
            'teacherWiseCollection' => $teacherWiseCollection,
            'paymentHistory' => $paymentHistory,
            'selectedTeacherId' => $teacherFilterId,
        ] + $filters);
    }

    /**
     * Display detailed active enrollment report.
     */
    public function enrollments(Request $request): View
    {
        $this->authorizeAcademicReportAccess($request);

        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status') ?: 'active';
        $filters = $this->baseFilters($request, includeTeachers: true);
        $teacherFilterId = $this->selectedTeacherId($request, $filters['teacherScopeId']);

        $enrollments = $this->enrollmentBaseQuery($filters['accessibleBatchIds'], $filters['classId'], $filters['batchId'], $teacherFilterId)
            ->when(in_array($status, ['active', 'withdrawn'], true), fn (Builder $query) => $query->where('enrollments.status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($search): void {
                    $studentQuery->where(function (Builder $subQuery) use ($search): void {
                        $subQuery
                            ->where('students.student_code', 'like', "%{$search}%")
                            ->orWhere('students.name', 'like', "%{$search}%")
                            ->orWhere('students.phone', 'like', "%{$search}%")
                            ->orWhere('students.guardian_phone', 'like', "%{$search}%");
                    });
                });
            })
            ->with(['student.academicClass', 'batch.academicClass', 'batch.subject', 'batch.teachers.user'])
            ->latest('enrollments.start_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.enrollments', [
            'enrollments' => $enrollments,
            'search' => $search,
            'status' => $status,
            'selectedTeacherId' => $teacherFilterId,
        ] + $filters);
    }

    /**
     * Display teacher finance report.
     */
    public function teacherFinance(Request $request): View
    {
        $this->authorizeFinanceReportAccess($request);

        $filters = $this->baseFilters($request, includeTeachers: true);
        $teacherFilterId = $this->selectedTeacherId($request, $filters['teacherScopeId']);
        $monthStart = $filters['month'].'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $teacherSummaries = $this->teacherOptionsQuery($filters['accessibleBatchIds'])
            ->when($teacherFilterId, fn (Builder $query, int $teacherId) => $query->where('teachers.id', $teacherId))
            ->get()
            ->map(function (Teacher $teacher) use ($filters, $monthStart, $monthEnd) {
                $distributionQuery = Distribution::query()
                    ->withSum('settlementItems', 'amount')
                    ->join('payments', 'payments.id', '=', 'distributions.payment_id')
                    ->join('enrollments', 'enrollments.id', '=', 'payments.enrollment_id')
                    ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
                    ->where('distributions.teacher_id', $teacher->id)
                    ->whereBetween('payments.payment_date', [$monthStart, $monthEnd])
                    ->when($filters['accessibleBatchIds'] !== null, fn (Builder $query) => $query->whereIn('batches.id', $filters['accessibleBatchIds']))
                    ->when($filters['classId'], fn (Builder $query, int $classId) => $query->where('batches.class_id', $classId))
                    ->when($filters['batchId'], fn (Builder $query, int $batchId) => $query->where('batches.id', $batchId));

                $periodDistributions = (clone $distributionQuery)->get();
                $earned = (float) $periodDistributions->sum('amount');
                $settledWithinPeriod = (float) TeacherSettlement::query()
                    ->join('teacher_settlement_items', 'teacher_settlement_items.teacher_settlement_id', '=', 'teacher_settlements.id')
                    ->join('distributions', 'distributions.id', '=', 'teacher_settlement_items.distribution_id')
                    ->join('payments', 'payments.id', '=', 'distributions.payment_id')
                    ->join('enrollments', 'enrollments.id', '=', 'payments.enrollment_id')
                    ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
                    ->where('teacher_settlements.teacher_id', $teacher->id)
                    ->whereBetween('teacher_settlements.settlement_date', [$monthStart, $monthEnd])
                    ->when($filters['accessibleBatchIds'] !== null, fn (Builder $query) => $query->whereIn('batches.id', $filters['accessibleBatchIds']))
                    ->when($filters['classId'], fn (Builder $query, int $classId) => $query->where('batches.class_id', $classId))
                    ->when($filters['batchId'], fn (Builder $query, int $batchId) => $query->where('batches.id', $batchId))
                    ->sum('teacher_settlement_items.amount');

                $outstanding = (float) Distribution::query()
                    ->withSum('settlementItems', 'amount')
                    ->join('payments', 'payments.id', '=', 'distributions.payment_id')
                    ->join('enrollments', 'enrollments.id', '=', 'payments.enrollment_id')
                    ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
                    ->where('distributions.teacher_id', $teacher->id)
                    ->when($filters['accessibleBatchIds'] !== null, fn (Builder $query) => $query->whereIn('batches.id', $filters['accessibleBatchIds']))
                    ->when($filters['classId'], fn (Builder $query, int $classId) => $query->where('batches.class_id', $classId))
                    ->when($filters['batchId'], fn (Builder $query, int $batchId) => $query->where('batches.id', $batchId))
                    ->get()
                    ->sum(fn (Distribution $distribution) => max(0, (float) $distribution->amount - (float) ($distribution->settlement_items_sum_amount ?? 0)));

                return [
                    'teacher_name' => $teacher->user?->name,
                    'teacher_id' => $teacher->id,
                    'earned' => $earned,
                    'settled' => $settledWithinPeriod,
                    'outstanding' => max(0, $outstanding),
                ];
            });

        $settlementHistory = TeacherSettlement::query()
            ->with(['teacher.user', 'payer'])
            ->when($teacherFilterId, fn (Builder $query, int $teacherId) => $query->where('teacher_id', $teacherId))
            ->when($filters['teacherScopeId'], fn (Builder $query, int $teacherId) => $query->where('teacher_id', $teacherId))
            ->whereBetween('settlement_date', [$monthStart, $monthEnd])
            ->latest('settlement_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.teacher-finance', [
            'teacherSummaries' => $teacherSummaries,
            'settlementHistory' => $settlementHistory,
            'selectedTeacherId' => $teacherFilterId,
        ] + $filters);
    }

    /**
     * Display expense report.
     */
    public function expenses(Request $request): View
    {
        $this->authorizeFinanceReportAccess($request);

        $search = trim((string) $request->string('search'));
        $type = (string) $request->string('type');
        $filters = $this->baseFilters($request, includeTeachers: true);
        $teacherFilterId = $this->selectedTeacherId($request, $filters['teacherScopeId']);
        $monthStart = $filters['month'].'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $summary = $this->expenseBaseQuery($request, $teacherFilterId)
            ->whereBetween('expenses.expense_date', [$monthStart, $monthEnd])
            ->when(in_array($type, ['common', 'teacher'], true), fn (Builder $query) => $query->where('expenses.type', $type))
            ->selectRaw('expenses.type as expense_type, COUNT(expenses.id) as entry_count, SUM(expenses.amount) as total_amount')
            ->groupBy('expenses.type')
            ->orderBy('expenses.type')
            ->get();

        $expenses = $this->expenseBaseQuery($request, $teacherFilterId)
            ->with(['teacher.user', 'creator'])
            ->whereBetween('expenses.expense_date', [$monthStart, $monthEnd])
            ->when(in_array($type, ['common', 'teacher'], true), fn (Builder $query) => $query->where('expenses.type', $type))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('expenses.note', 'like', "%{$search}%")
                        ->orWhereHas('teacher.user', fn (Builder $teacherQuery) => $teacherQuery->where('users.name', 'like', "%{$search}%"));
                });
            })
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.expenses', [
            'summary' => $summary,
            'expenses' => $expenses,
            'search' => $search,
            'type' => $type,
            'selectedTeacherId' => $teacherFilterId,
        ] + $filters);
    }

    /**
     * Ensure the user can access reports.
     */
    protected function authorizeReportAccess(Request $request): void
    {
        abort_unless($request->user()?->can('view reports') || $request->user()?->hasRole('Teacher'), Response::HTTP_FORBIDDEN);
    }

    /**
     * Ensure the user can access academic reports.
     */
    protected function authorizeAcademicReportAccess(Request $request): void
    {
        $this->authorizeReportAccess($request);

        abort_unless($this->canViewAcademicReports($request), Response::HTTP_FORBIDDEN);
    }

    /**
     * Ensure the user can access finance reports.
     */
    protected function authorizeFinanceReportAccess(Request $request): void
    {
        $this->authorizeReportAccess($request);

        abort_unless($this->canViewFinanceReports($request), Response::HTTP_FORBIDDEN);
    }

    /**
     * Check if the current user can see academic reports.
     */
    protected function canViewAcademicReports(Request $request): bool
    {
        return $request->user()->hasAnyRole(['Super Admin', 'Admin', 'Teacher']);
    }

    /**
     * Check if the current user can see finance reports.
     */
    protected function canViewFinanceReports(Request $request): bool
    {
        return $request->user()->hasAnyRole(['Super Admin', 'Admin', 'Accounts', 'Teacher']);
    }

    /**
     * Resolve common report filters and option lists.
     */
    protected function baseFilters(Request $request, bool $includeTeachers = false): array
    {
        $this->authorizeReportAccess($request);

        $month = (string) $request->string('month') ?: now()->format('Y-m');
        $classId = $request->integer('class_id') ?: null;
        $batchId = $request->integer('batch_id') ?: null;
        $accessibleBatchIds = $this->accessibleBatchIds($request);
        $teacherScopeId = $request->user()->hasRole('Teacher') ? $request->user()->teacherProfile?->id : null;

        $batchOptions = Batch::query()
            ->with('academicClass')
            ->when($accessibleBatchIds !== null, fn (Builder $query) => $query->whereIn('id', $accessibleBatchIds))
            ->when($classId, fn (Builder $query, int $selectedClassId) => $query->where('class_id', $selectedClassId))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $classOptions = AcademicClass::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return [
            'month' => $month,
            'classId' => $classId,
            'batchId' => $batchId,
            'accessibleBatchIds' => $accessibleBatchIds,
            'teacherScopeId' => $teacherScopeId,
            'classOptions' => $classOptions,
            'batchOptions' => $batchOptions,
            'teacherOptions' => $includeTeachers ? $this->teacherOptionsQuery($accessibleBatchIds)->get() : collect(),
        ];
    }

    /**
     * Resolve the selected teacher filter.
     */
    protected function selectedTeacherId(Request $request, ?int $teacherScopeId): ?int
    {
        return $teacherScopeId ?: ($request->integer('teacher_id') ?: null);
    }

    /**
     * Get the accessible batch ids for a teacher, otherwise null.
     */
    protected function accessibleBatchIds(Request $request): ?array
    {
        if ($request->user()->hasRole('Teacher')) {
            $teacher = $request->user()->teacherProfile;

            abort_if(! $teacher, Response::HTTP_FORBIDDEN);

            return $teacher->batches()->pluck('batches.id')->all();
        }

        return null;
    }

    /**
     * Teacher dropdown query for reports.
     */
    protected function teacherOptionsQuery(?array $accessibleBatchIds): Builder
    {
        return Teacher::query()
            ->select('teachers.*')
            ->with('user')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.status', 'active')
            ->where('users.status', 'active')
            ->when($accessibleBatchIds !== null, function (Builder $query) use ($accessibleBatchIds): void {
                $query->whereHas('batches', fn (Builder $batchQuery) => $batchQuery->whereIn('batches.id', $accessibleBatchIds));
            })
            ->orderBy('users.name');
    }

    /**
     * Approved payments summary base query with shared filters.
     */
    protected function approvedPaymentsSummaryQuery(?array $accessibleBatchIds, ?int $classId, ?int $batchId, ?int $teacherId)
    {
        return Payment::query()
            ->join('enrollments', 'enrollments.id', '=', 'payments.enrollment_id')
            ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
            ->join('classes', 'classes.id', '=', 'batches.class_id')
            ->where('payments.status', 'approved')
            ->when($accessibleBatchIds !== null, fn (Builder $query) => $query->whereIn('batches.id', $accessibleBatchIds))
            ->when($classId, fn (Builder $query, int $selectedClassId) => $query->where('batches.class_id', $selectedClassId))
            ->when($batchId, fn (Builder $query, int $selectedBatchId) => $query->where('batches.id', $selectedBatchId))
            ->when($teacherId, function (Builder $query, int $selectedTeacherId): void {
                $query->whereExists(function ($subQuery) use ($selectedTeacherId): void {
                    $subQuery->select(DB::raw(1))
                        ->from('batch_teacher')
                        ->whereColumn('batch_teacher.batch_id', 'batches.id')
                        ->where('batch_teacher.teacher_id', $selectedTeacherId);
                });
            });
    }

    /**
     * Approved payments detail query with eager loads.
     */
    protected function approvedPaymentsDetailQuery(?array $accessibleBatchIds, ?int $classId, ?int $batchId, ?int $teacherId)
    {
        return Payment::query()
            ->with([
                'collector',
                'batchFee.feeType',
                'enrollment.student.academicClass',
                'enrollment.batch.academicClass',
                'enrollment.batch.subject',
            ])
            ->where('payments.status', 'approved')
            ->when($accessibleBatchIds !== null, function (Builder $query) use ($accessibleBatchIds): void {
                $query->whereHas('enrollment.batch', fn (Builder $batchQuery) => $batchQuery->whereIn('batches.id', $accessibleBatchIds));
            })
            ->when($classId, function (Builder $query, int $selectedClassId): void {
                $query->whereHas('enrollment.batch', fn (Builder $batchQuery) => $batchQuery->where('class_id', $selectedClassId));
            })
            ->when($batchId, function (Builder $query, int $selectedBatchId): void {
                $query->whereHas('enrollment', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('batch_id', $selectedBatchId));
            })
            ->when($teacherId, function (Builder $query, int $selectedTeacherId): void {
                $query->whereHas('enrollment.batch.teachers', fn (Builder $teacherQuery) => $teacherQuery->where('teachers.id', $selectedTeacherId));
            });
    }

    /**
     * Base expense query by user scope.
     */
    protected function expenseBaseQuery(Request $request, ?int $teacherId)
    {
        return Expense::query()
            ->when($request->user()->hasRole('Teacher'), function (Builder $query) use ($request): void {
                $teacher = $request->user()->teacherProfile;

                abort_if(! $teacher, Response::HTTP_FORBIDDEN);

                $query->where('expenses.teacher_id', $teacher->id);
            })
            ->when($teacherId, fn (Builder $query, int $selectedTeacherId) => $query->where('expenses.teacher_id', $selectedTeacherId));
    }

    /**
     * Base enrollment query with shared filters.
     */
    protected function enrollmentBaseQuery(?array $accessibleBatchIds, ?int $classId, ?int $batchId, ?int $teacherId)
    {
        return Enrollment::query()
            ->when($accessibleBatchIds !== null, fn (Builder $query) => $query->whereIn('enrollments.batch_id', $accessibleBatchIds))
            ->when($classId, fn (Builder $query, int $selectedClassId) => $query->whereHas('batch', fn (Builder $batchQuery) => $batchQuery->where('class_id', $selectedClassId)))
            ->when($batchId, fn (Builder $query, int $selectedBatchId) => $query->where('enrollments.batch_id', $selectedBatchId))
            ->when($teacherId, fn (Builder $query, int $selectedTeacherId) => $query->whereHas('batch.teachers', fn (Builder $teacherQuery) => $teacherQuery->where('teachers.id', $selectedTeacherId)));
    }
}
