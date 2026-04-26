<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BatchRequest;
use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BatchController extends Controller
{
    /**
     * Display a listing of batches.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $batches = $this->accessibleBatchesQuery($request)
            ->with(['academicClass', 'subject', 'teachers'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhereHas('academicClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('teachers.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.batches.index', compact('batches', 'search'));
    }

    /**
     * Display the specified batch.
     */
    public function show(Request $request, Batch $batch): View
    {
        abort_unless($this->canAccessBatch($request, $batch), Response::HTTP_FORBIDDEN);

        $batch->load(['academicClass', 'subject', 'teachers.user']);

        return view('admin.batches.show', compact('batch'));
    }

    /**
     * Show the form for creating a new batch.
     */
    public function create(): View
    {
        abort_unless(auth()->user()?->can('manage batches'), Response::HTTP_FORBIDDEN);

        return view('admin.batches.create', $this->formData());
    }

    /**
     * Store a newly created batch in storage.
     */
    public function store(BatchRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $batch = Batch::create([
            'name' => $validated['name'],
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'] ?? null,
            'monthly_fee' => $validated['monthly_fee'],
            'distribution_type' => $validated['distribution_type'],
            'schedule_days' => $validated['schedule_days'] ?? null,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'status' => $validated['status'],
        ]);

        $batch->teachers()->sync($validated['teacher_ids']);

        return redirect()
            ->route('admin.batches.index')
            ->with('success', 'Batch created successfully.');
    }

    /**
     * Show the form for editing the specified batch.
     */
    public function edit(Batch $batch): View
    {
        abort_unless(auth()->user()?->can('manage batches'), Response::HTTP_FORBIDDEN);

        $batch->load('teachers');

        return view('admin.batches.edit', array_merge(
            $this->formData(),
            ['batch' => $batch],
        ));
    }

    /**
     * Update the specified batch in storage.
     */
    public function update(BatchRequest $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validated();

        $batch->update([
            'name' => $validated['name'],
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'] ?? null,
            'monthly_fee' => $validated['monthly_fee'],
            'distribution_type' => $validated['distribution_type'],
            'schedule_days' => $validated['schedule_days'] ?? null,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'status' => $validated['status'],
        ]);

        $batch->teachers()->sync($validated['teacher_ids']);

        return redirect()
            ->route('admin.batches.index')
            ->with('success', 'Batch updated successfully.');
    }

    /**
     * Build the accessible batches query by current user scope.
     */
    protected function accessibleBatchesQuery(Request $request)
    {
        $query = Batch::query();

        if ($request->user()->can('manage batches')) {
            return $query;
        }

        $teacher = $request->user()->teacherProfile;

        abort_if(! $teacher, Response::HTTP_FORBIDDEN);

        return $query->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
    }

    /**
     * Determine whether the authenticated user can access this batch.
     */
    protected function canAccessBatch(Request $request, Batch $batch): bool
    {
        if ($request->user()->can('manage batches')) {
            return true;
        }

        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return false;
        }

        return $batch->teachers()->where('teachers.id', $teacher->id)->exists();
    }

    /**
     * Shared data for batch forms.
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'classes' => AcademicClass::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'subjects' => Subject::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'teachers' => Teacher::query()
                ->with('user')
                ->where('teachers.status', 'active')
                ->whereHas('user', fn ($query) => $query->where('status', 'active'))
                ->join('users', 'users.id', '=', 'teachers.user_id')
                ->orderBy('users.name')
                ->select('teachers.*')
                ->get(),
        ];
    }
}
