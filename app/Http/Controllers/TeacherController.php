<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Teacher::class);

        $teachers = Teacher::query()
            ->with('user')
            ->visibleTo($request->user())
            ->latest()
            ->paginate(12);

        return view('teachers.index', [
            'teachers' => $teachers,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Teacher::class);

        return view('teachers.create', [
            'availableUsers' => $this->availableTeacherUsers($request->user()),
            'teacher' => new Teacher([
                'status' => Teacher::STATUS_ACTIVE,
                'can_own_batches' => true,
                'can_collect_fees' => true,
            ]),
        ]);
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $teacher = Teacher::create($this->validatedPayload($request));

        return redirect()
            ->route('teachers.edit', $teacher)
            ->with('status', 'Teacher profile created successfully.');
    }

    public function edit(Request $request, Teacher $teacher): View
    {
        $this->authorize('update', $teacher);

        return view('teachers.edit', [
            'teacher' => $teacher->load('user'),
            'availableUsers' => $this->availableTeacherUsers($request->user(), $teacher),
            'isSelfManaged' => $request->user()->isTeacher() && $teacher->isOwnedBy($request->user()),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update($this->validatedPayload($request, $request->user()->isTeacher()));

        return redirect()
            ->route('teachers.edit', $teacher)
            ->with('status', 'Teacher profile updated successfully.');
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);

        $teacher->delete();

        return redirect()
            ->route('teachers.index')
            ->with('status', 'Teacher profile deleted successfully.');
    }

    protected function availableTeacherUsers(User $user, ?Teacher $teacher = null)
    {
        return User::role(Role::TEACHER)
            ->where('tenant_id', $user->tenant_id)
            ->where(function ($query) use ($teacher): void {
                $query->whereDoesntHave('teacher')
                    ->when($teacher?->user_id, fn ($subQuery) => $subQuery->orWhere('id', $teacher->user_id));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedPayload(StoreTeacherRequest|UpdateTeacherRequest $request, bool $selfManaged = false): array
    {
        $validated = $request->validated();
        $validated['subject_specializations'] = $this->normalizeSpecializations($validated['subject_specializations'] ?? null);
        $validated['can_own_batches'] = (bool) ($validated['can_own_batches'] ?? false);
        $validated['can_collect_fees'] = (bool) ($validated['can_collect_fees'] ?? false);

        if ($selfManaged) {
            return array_merge(
                ['tenant_id' => $request->user()->tenant_id],
                collect($validated)->only([
                    'name',
                    'phone',
                    'email',
                    'subject_specializations',
                    'address',
                    'bio',
                ])->all(),
            );
        }

        $validated['tenant_id'] = $request->user()->tenant_id;

        return $validated;
    }

    /**
     * @return array<int, string>|null
     */
    protected function normalizeSpecializations(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
