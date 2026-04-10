<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Guardian;
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $students = Student::query()
            ->with(['user', 'ownerTeacher', 'guardians'])
            ->visibleTo($request->user())
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->string('q'));

                $query->where(function (Builder $studentQuery) use ($search): void {
                    $studentQuery
                        ->where('student_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('institution_name', 'like', "%{$search}%")
                        ->orWhereHas('guardians', function (Builder $guardianQuery) use ($search): void {
                            $guardianQuery
                                ->where('guardians.name', 'like', "%{$search}%")
                                ->orWhere('guardians.phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when(
                $request->filled('owner_teacher_id') && $request->user()->isAdmin(),
                fn (Builder $query) => $query->where('owner_teacher_id', $request->integer('owner_teacher_id'))
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'teacherOwners' => $this->availableTeacherOwners($request->user()),
            'statuses' => $this->studentStatuses(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Student::class);

        $teacher = $request->user()->isTeacher() ? $request->user()->teacher : null;

        return view('students.create', [
            'student' => new Student([
                'status' => Student::STATUS_ACTIVE,
                'admission_date' => now()->toDateString(),
                'owner_teacher_id' => $teacher?->getKey(),
            ]),
            'teacherOwners' => $this->availableTeacherOwners($request->user()),
            'availableUsers' => $this->availableStudentUsers($request->user()),
            'availableGuardianUsers' => $this->availableGuardianUsers($request->user()),
            'guardian' => null,
            'relationTypes' => Guardian::relationTypes(),
            'isTeacherManaged' => $request->user()->isTeacher(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = Student::create($this->validatedPayload($request));
        $this->syncPrimaryGuardian($student, $request);

        return redirect()
            ->route('students.edit', $student)
            ->with('status', 'Student profile created successfully.');
    }

    public function edit(Request $request, Student $student): View
    {
        $this->authorize('update', $student);

        return view('students.edit', [
            'student' => $student->load(['user', 'ownerTeacher', 'guardians']),
            'teacherOwners' => $this->availableTeacherOwners($request->user()),
            'availableUsers' => $this->availableStudentUsers($request->user(), $student),
            'availableGuardianUsers' => $this->availableGuardianUsers($request->user(), $student->primaryGuardian()),
            'guardian' => $student->primaryGuardian(),
            'relationTypes' => Guardian::relationTypes(),
            'isTeacherManaged' => $request->user()->isTeacher(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($this->validatedPayload($request));
        $this->syncPrimaryGuardian($student, $request);

        return redirect()
            ->route('students.edit', $student)
            ->with('status', 'Student profile updated successfully.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('status', 'Student profile deleted successfully.');
    }

    protected function availableTeacherOwners(User $user)
    {
        return Teacher::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('status', Teacher::STATUS_ACTIVE)
            ->where('can_own_batches', true)
            ->when($user->isTeacher(), fn (Builder $query) => $query->whereKey($user->teacher?->getKey()))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function availableStudentUsers(User $user, ?Student $student = null)
    {
        return User::role(Role::STUDENT)
            ->where('tenant_id', $user->tenant_id)
            ->where(function (Builder $query) use ($student): void {
                $query->whereDoesntHave('student')
                    ->when($student?->user_id, fn (Builder $subQuery) => $subQuery->orWhere('id', $student->user_id));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    protected function availableGuardianUsers(User $user, ?Guardian $guardian = null)
    {
        return User::role(Role::GUARDIAN)
            ->where('tenant_id', $user->tenant_id)
            ->where(function (Builder $query) use ($guardian): void {
                $query->whereDoesntHave('guardian')
                    ->when($guardian?->user_id, fn (Builder $subQuery) => $subQuery->orWhere('id', $guardian->user_id));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * @return array<string, string>
     */
    protected function studentStatuses(): array
    {
        return [
            Student::STATUS_ACTIVE => 'Active',
            Student::STATUS_INACTIVE => 'Inactive',
            Student::STATUS_DROPOUT => 'Dropout',
            Student::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedPayload(StoreStudentRequest|UpdateStudentRequest $request): array
    {
        $validated = collect($request->validated())->only([
            'user_id',
            'owner_teacher_id',
            'student_code',
            'name',
            'phone',
            'email',
            'admission_date',
            'status',
            'institution_name',
            'institution_class',
            'address',
            'notes',
        ])->all();

        $validated['tenant_id'] = $request->user()->tenant_id;

        if ($request->user()->isTeacher() && $request->user()->teacher) {
            $validated['owner_teacher_id'] = $request->user()->teacher->getKey();
        }

        return $validated;
    }

    protected function syncPrimaryGuardian(Student $student, StoreStudentRequest|UpdateStudentRequest $request): void
    {
        $student->loadMissing('guardians');

        $guardianPayload = collect($request->validated())->only([
            'guardian_user_id',
            'guardian_name',
            'guardian_phone',
            'guardian_email',
            'guardian_relation_type',
            'guardian_occupation',
            'guardian_address',
            'guardian_notes',
        ]);

        $hasGuardianData = $guardianPayload
            ->except(['guardian_notes'])
            ->contains(fn ($value) => filled($value));

        $currentPrimaryGuardian = $student->primaryGuardian();

        $student->guardians()->newPivotStatement()
            ->where('student_id', $student->getKey())
            ->update(['is_primary' => false]);

        if (! $hasGuardianData) {
            return;
        }

        $guardian = $currentPrimaryGuardian;

        if (! $guardian && $guardianPayload['guardian_user_id']) {
            $guardian = Guardian::query()
                ->where('tenant_id', $student->tenant_id)
                ->where('user_id', $guardianPayload['guardian_user_id'])
                ->first();
        }

        if ($guardian) {
            $guardian->update([
                'user_id' => $guardianPayload['guardian_user_id'],
                'name' => $guardianPayload['guardian_name'],
                'phone' => $guardianPayload['guardian_phone'],
                'email' => $guardianPayload['guardian_email'],
                'occupation' => $guardianPayload['guardian_occupation'],
                'address' => $guardianPayload['guardian_address'],
            ]);
        } else {
            $guardian = Guardian::query()->create([
                'tenant_id' => $student->tenant_id,
                'user_id' => $guardianPayload['guardian_user_id'],
                'name' => $guardianPayload['guardian_name'],
                'phone' => $guardianPayload['guardian_phone'],
                'email' => $guardianPayload['guardian_email'],
                'occupation' => $guardianPayload['guardian_occupation'],
                'address' => $guardianPayload['guardian_address'],
            ]);
        }

        if ($student->guardians()->whereKey($guardian->getKey())->exists()) {
            $student->guardians()->updateExistingPivot($guardian->getKey(), [
                'tenant_id' => $student->tenant_id,
                'relation_type' => $guardianPayload['guardian_relation_type'],
                'is_primary' => true,
                'notes' => $guardianPayload['guardian_notes'],
            ]);

            return;
        }

        $student->guardians()->attach($guardian->getKey(), [
            'tenant_id' => $student->tenant_id,
            'relation_type' => $guardianPayload['guardian_relation_type'],
            'is_primary' => true,
            'notes' => $guardianPayload['guardian_notes'],
        ]);
    }
}
