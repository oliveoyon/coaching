<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchScheduleRequest;
use App\Http\Requests\UpdateBatchScheduleRequest;
use App\Models\Batch;
use App\Models\BatchSchedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BatchSchedule::class);

        $schedules = BatchSchedule::query()
            ->with(['batch.subject', 'subject', 'teacher'])
            ->visibleTo($request->user())
            ->when($request->filled('batch_id'), fn (Builder $query) => $query->where('batch_id', $request->integer('batch_id')))
            ->when($request->filled('day_of_week'), fn (Builder $query) => $query->where('day_of_week', $request->string('day_of_week')))
            ->when($request->filled('teacher_id'), fn (Builder $query) => $query->where('teacher_id', $request->integer('teacher_id')))
            ->orderByRaw("FIELD(day_of_week, 'saturday','sunday','monday','tuesday','wednesday','thursday','friday')")
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        return view('schedules.index', [
            'schedules' => $schedules,
            'batches' => $this->batches($request),
            'teachers' => $this->teachers($request),
            'days' => BatchSchedule::DAYS,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', BatchSchedule::class);

        $selectedBatch = $request->filled('batch_id')
            ? Batch::query()->visibleTo($request->user())->find($request->integer('batch_id'))
            : null;

        return view('schedules.create', [
            'schedule' => new BatchSchedule([
                'day_of_week' => now()->englishDayOfWeek ? strtolower(now()->englishDayOfWeek) : 'saturday',
                'session_type' => BatchSchedule::SESSION_TYPE_REGULAR,
                'sort_order' => 1,
            ]),
            'batches' => $this->batches($request),
            'subjects' => $this->subjects($request),
            'teachers' => $this->teachers($request, $selectedBatch),
            'days' => BatchSchedule::DAYS,
            'sessionTypes' => BatchSchedule::sessionTypes(),
            'selectedBatch' => $selectedBatch,
            'isTeacherScoped' => $request->user()->isTeacher(),
        ]);
    }

    public function store(StoreBatchScheduleRequest $request): RedirectResponse
    {
        $schedule = BatchSchedule::query()->create($this->payload($request));

        return redirect()
            ->route('schedules.index', ['batch_id' => $schedule->batch_id])
            ->with('status', 'Routine schedule created successfully.');
    }

    public function edit(Request $request, BatchSchedule $schedule): View
    {
        $this->authorize('update', $schedule);

        return view('schedules.edit', [
            'schedule' => $schedule->load(['batch', 'subject', 'teacher']),
            'batches' => $this->batches($request),
            'subjects' => $this->subjects($request),
            'teachers' => $this->teachers($request, $schedule->batch),
            'days' => BatchSchedule::DAYS,
            'sessionTypes' => BatchSchedule::sessionTypes(),
            'selectedBatch' => $schedule->batch,
            'isTeacherScoped' => $request->user()->isTeacher(),
        ]);
    }

    public function update(UpdateBatchScheduleRequest $request, BatchSchedule $schedule): RedirectResponse
    {
        $schedule->update($this->payload($request));

        return redirect()
            ->route('schedules.index', ['batch_id' => $schedule->batch_id])
            ->with('status', 'Routine schedule updated successfully.');
    }

    public function destroy(Request $request, BatchSchedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);
        $batchId = $schedule->batch_id;
        $schedule->delete();

        return redirect()
            ->route('schedules.index', ['batch_id' => $batchId])
            ->with('status', 'Routine schedule deleted successfully.');
    }

    protected function payload(StoreBatchScheduleRequest|UpdateBatchScheduleRequest $request): array
    {
        $validated = $request->validated();
        $batch = Batch::query()->findOrFail($validated['batch_id']);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['subject_id'] = $validated['subject_id'] ?: $batch->subject_id;
        $validated['teacher_id'] = $request->user()->isTeacher() && $request->user()->teacher
            ? $request->user()->teacher->getKey()
            : $validated['teacher_id'];
        $validated['is_extra'] = (bool) ($validated['is_extra'] ?? false);
        $validated['session_type'] = $validated['is_extra']
            ? BatchSchedule::SESSION_TYPE_EXTRA
            : $validated['session_type'];
        $validated['sort_order'] = $validated['sort_order'] ?? 1;

        return $validated;
    }

    protected function batches(Request $request)
    {
        return Batch::query()
            ->visibleTo($request->user())
            ->where('status', Batch::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'subject_id', 'owner_teacher_id']);
    }

    protected function subjects(Request $request)
    {
        return Subject::query()
            ->forCurrentTenant()
            ->where('status', Subject::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    protected function teachers(Request $request, ?Batch $batch = null)
    {
        $query = Teacher::query()
            ->forCurrentTenant()
            ->where('status', Teacher::STATUS_ACTIVE);

        if ($request->user()->isTeacher() && $request->user()->teacher) {
            $query->whereKey($request->user()->teacher->getKey());
        } elseif ($batch?->owner_teacher_id) {
            $query->where(function ($inner) use ($batch) {
                $inner->whereKey($batch->owner_teacher_id)
                    ->orWhere('can_own_batches', true);
            });
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }
}
