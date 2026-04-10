<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentEnrollmentRequest;
use App\Http\Requests\UpdateStudentEnrollmentRequest;
use App\Models\Batch;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentEnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentEnrollment::class);

        $enrollments = StudentEnrollment::query()
            ->with(['student.ownerTeacher', 'batch.ownerTeacher'])
            ->visibleTo($request->user())
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->string('q'));

                $query->where(function (Builder $enrollmentQuery) use ($search): void {
                    $enrollmentQuery
                        ->whereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('batch', function (Builder $batchQuery) use ($search): void {
                            $batchQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('batch_id'), fn (Builder $query) => $query->where('batch_id', $request->integer('batch_id')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'batches' => $this->availableBatches($request->user()),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentEnrollment::class);

        return view('enrollments.create', [
            'enrollment' => new StudentEnrollment([
                'status' => StudentEnrollment::STATUS_ACTIVE,
                'enrolled_at' => now()->toDateString(),
                'student_id' => $request->integer('student_id') ?: null,
                'batch_id' => $request->integer('batch_id') ?: null,
            ]),
            'students' => $this->availableStudents($request->user()),
            'batches' => $this->availableBatches($request->user()),
            'isTeacherScoped' => $request->user()->isTeacher(),
        ]);
    }

    public function store(StoreStudentEnrollmentRequest $request): RedirectResponse
    {
        $enrollment = StudentEnrollment::create($this->validatedPayload($request));

        return redirect()
            ->route('enrollments.edit', $enrollment)
            ->with('status', 'Student enrolled successfully.');
    }

    public function edit(Request $request, StudentEnrollment $enrollment): View
    {
        $this->authorize('update', $enrollment);

        return view('enrollments.edit', [
            'enrollment' => $enrollment->load(['student.ownerTeacher', 'batch.ownerTeacher']),
            'students' => $this->availableStudents($request->user()),
            'batches' => $this->availableBatches($request->user()),
            'isTeacherScoped' => $request->user()->isTeacher(),
        ]);
    }

    public function update(UpdateStudentEnrollmentRequest $request, StudentEnrollment $enrollment): RedirectResponse
    {
        $enrollment->update($this->validatedPayload($request));

        return redirect()
            ->route('enrollments.edit', $enrollment)
            ->with('status', 'Enrollment updated successfully.');
    }

    public function destroy(Request $request, StudentEnrollment $enrollment): RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        $enrollment->delete();

        return redirect()
            ->route('enrollments.index')
            ->with('status', 'Enrollment removed successfully.');
    }

    protected function availableStudents($user)
    {
        return Student::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->get(['id', 'name', 'student_code', 'owner_teacher_id']);
    }

    protected function availableBatches($user)
    {
        return Batch::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'owner_teacher_id']);
    }

    /**
     * @return array<string, string>
     */
    protected function statuses(): array
    {
        return [
            StudentEnrollment::STATUS_ACTIVE => 'Active',
            StudentEnrollment::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedPayload(StoreStudentEnrollmentRequest|UpdateStudentEnrollmentRequest $request): array
    {
        return array_merge($request->validated(), [
            'tenant_id' => $request->user()->tenant_id,
        ]);
    }
}
