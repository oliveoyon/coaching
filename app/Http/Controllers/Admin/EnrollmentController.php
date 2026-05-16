<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoteEnrollmentRequest;
use App\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Http\Requests\Admin\WithdrawEnrollmentRequest;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\EnrollmentPromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Display enrollment history.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');

        $enrollments = $this->accessibleEnrollmentsQuery($request)
            ->with(['student.academicClass', 'batch.academicClass', 'batch.subject', 'creator'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->whereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('guardian_phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['active', 'withdrawn', 'completed'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.enrollments.index', compact('enrollments', 'search', 'status'));
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create(Request $request): View
    {
        abort_unless(auth()->user()?->can('manage enrollments'), Response::HTTP_FORBIDDEN);

        $studentSearch = trim((string) old('student_search', (string) $request->string('student_search')));
        $selectedStudentId = (int) old('student_id', $request->integer('student_id'));
        $changeStudent = old('change_student', $request->boolean('change_student'));
        $selectedStudent = $selectedStudentId > 0
            ? Student::query()->with('academicClass')->find($selectedStudentId)
            : null;

        $students = collect();

        if ($studentSearch !== '' && (! $selectedStudent || $changeStudent)) {
            $students = Student::query()
                ->with('academicClass')
                ->where('status', 'active')
                ->where(function ($query) use ($studentSearch) {
                    $query->where('student_code', 'like', "%{$studentSearch}%")
                        ->orWhere('name', 'like', "%{$studentSearch}%")
                        ->orWhere('phone', 'like', "%{$studentSearch}%")
                        ->orWhere('guardian_phone', 'like', "%{$studentSearch}%");
                })
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        return view('admin.enrollments.create', [
            'students' => $students,
            'studentSearch' => $studentSearch,
            'selectedStudent' => $selectedStudent,
            'changeStudent' => $changeStudent,
            'batches' => $this->formBatches(),
        ]);
    }

    /**
     * Store a newly created enrollment in storage.
     */
    public function store(StoreEnrollmentRequest $request): RedirectResponse
    {
        Enrollment::create([
            'student_id' => $request->integer('student_id'),
            'batch_id' => $request->integer('batch_id'),
            'start_date' => $request->date('start_date')->format('Y-m-d'),
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.enrollments.index')
            ->with('success', 'Student enrolled successfully.');
    }

    /**
     * Show the bulk promotion screen.
     */
    public function promote(Request $request): View
    {
        abort_unless($request->user()?->can('manage enrollments'), Response::HTTP_FORBIDDEN);

        $sourceBatchId = $request->integer('source_batch_id') ?: null;
        $targetBatchId = $request->integer('target_batch_id') ?: null;
        $studentSearch = trim((string) $request->string('student_search'));

        $sourceBatch = $sourceBatchId
            ? Batch::query()->with(['academicClass', 'subject', 'teachers.user'])->find($sourceBatchId)
            : null;

        $targetBatch = $targetBatchId
            ? Batch::query()->with(['academicClass', 'subject'])->withCount('batchFees')->find($targetBatchId)
            : null;

        $sourceEnrollments = collect();

        if ($sourceBatch) {
            $sourceEnrollments = Enrollment::query()
                ->with(['student.academicClass', 'batch.academicClass'])
                ->where('batch_id', $sourceBatch->id)
                ->where('status', 'active')
                ->when($studentSearch !== '', function ($query) use ($studentSearch) {
                    $query->whereHas('student', function ($studentQuery) use ($studentSearch) {
                        $studentQuery->where(function ($subQuery) use ($studentSearch) {
                            $subQuery
                                ->where('students.student_code', 'like', "%{$studentSearch}%")
                                ->orWhere('students.name', 'like', "%{$studentSearch}%")
                                ->orWhere('students.phone', 'like', "%{$studentSearch}%")
                                ->orWhere('students.guardian_phone', 'like', "%{$studentSearch}%");
                        });
                    });
                })
                ->get()
                ->sortBy(fn (Enrollment $enrollment) => $enrollment->student?->name)
                ->values();
        }

        return view('admin.enrollments.promote', [
            'sourceBatchId' => $sourceBatchId,
            'targetBatchId' => $targetBatchId,
            'studentSearch' => $studentSearch,
            'sourceBatch' => $sourceBatch,
            'targetBatch' => $targetBatch,
            'sourceEnrollments' => $sourceEnrollments,
            'sourceBatches' => $this->promotionBatches(),
            'targetBatches' => $this->promotionBatches(),
        ]);
    }

    /**
     * Store promoted enrollments in bulk.
     */
    public function storePromotion(PromoteEnrollmentRequest $request, EnrollmentPromotionService $promotionService): RedirectResponse
    {
        $result = $promotionService->promote(
            enrollmentIds: $request->collect('enrollment_ids')->map(fn ($id) => (int) $id)->all(),
            targetBatchId: $request->integer('target_batch_id'),
            startDate: $request->date('start_date'),
            endedAt: $request->filled('source_end_date') ? $request->date('source_end_date') : null,
            actorId: $request->user()->id,
        );

        return redirect()
            ->route('admin.enrollments.index', ['status' => 'active'])
            ->with('success', $result['count'].' student'.($result['count'] === 1 ? '' : 's').' promoted successfully.');
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Request $request, Enrollment $enrollment): View
    {
        abort_unless($this->canAccessEnrollment($request, $enrollment), Response::HTTP_FORBIDDEN);

        $enrollment->load(['student.academicClass', 'batch.academicClass', 'batch.subject', 'batch.teachers.user', 'creator']);

        return view('admin.enrollments.show', compact('enrollment'));
    }

    /**
     * Show the withdrawal form for an enrollment.
     */
    public function withdrawForm(Request $request, Enrollment $enrollment): View
    {
        abort_unless($request->user()?->can('manage enrollments'), Response::HTTP_FORBIDDEN);
        abort_unless($this->canAccessEnrollment($request, $enrollment), Response::HTTP_FORBIDDEN);
        abort_if($enrollment->status !== 'active', Response::HTTP_UNPROCESSABLE_ENTITY);

        $enrollment->load(['student', 'batch']);

        return view('admin.enrollments.withdraw', compact('enrollment'));
    }

    /**
     * Withdraw the specified enrollment.
     */
    public function withdraw(WithdrawEnrollmentRequest $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($this->canAccessEnrollment($request, $enrollment), Response::HTTP_FORBIDDEN);
        abort_if($enrollment->status !== 'active', Response::HTTP_UNPROCESSABLE_ENTITY);

        $enrollment->update([
            'status' => 'withdrawn',
            'end_date' => $request->date('end_date')->format('Y-m-d'),
        ]);

        return redirect()
            ->route('admin.enrollments.index')
            ->with('success', 'Student withdrawn from batch successfully.');
    }

    /**
     * Build the accessible enrollment query by user scope.
     */
    protected function accessibleEnrollmentsQuery(Request $request)
    {
        $query = Enrollment::query();

        if ($request->user()->can('manage enrollments')) {
            return $query;
        }

        $teacher = $request->user()->teacherProfile;

        abort_if(! $teacher, Response::HTTP_FORBIDDEN);

        return $query->whereHas('batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
    }

    /**
     * Determine whether the authenticated user can access this enrollment.
     */
    protected function canAccessEnrollment(Request $request, Enrollment $enrollment): bool
    {
        if ($request->user()->can('manage enrollments')) {
            return true;
        }

        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return false;
        }

        return $enrollment->batch()
            ->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id))
            ->exists();
    }

    /**
     * Get active batches for enrollment form.
     */
    protected function formBatches()
    {
        return Batch::query()
            ->with(['academicClass', 'subject'])
            ->withCount('batchFees')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active batches for promotion source/target selection.
     */
    protected function promotionBatches()
    {
        return Batch::query()
            ->with(['academicClass', 'subject', 'teachers.user'])
            ->withCount('batchFees')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
