<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\BatchSchedule;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Batch::class);

        $batches = Batch::query()
            ->with(['program', 'subject', 'ownerTeacher', 'schedules'])
            ->visibleTo($request->user())
            ->latest()
            ->paginate(12);

        return view('batches.index', [
            'batches' => $batches,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Batch::class);

        return view('batches.create', [
            'batch' => new Batch([
                'status' => Batch::STATUS_ACTIVE,
            ]),
            'programs' => $this->programs($request),
            'subjects' => $this->subjects($request),
            'ownerTeachers' => $this->ownerTeachers($request),
            'isTeacherScoped' => $request->user()->isTeacher(),
        ]);
    }

    public function store(StoreBatchRequest $request): RedirectResponse
    {
        $batch = DB::transaction(function () use ($request): Batch {
            $batch = Batch::create($this->validatedPayload($request));
            $this->syncSchedules($batch, $request);

            return $batch;
        });

        return redirect()
            ->route('batches.edit', $batch)
            ->with('status', 'Batch created successfully.');
    }

    public function edit(Request $request, Batch $batch): View
    {
        $this->authorize('update', $batch);

        return view('batches.edit', [
            'batch' => $batch->load(['schedules', 'program', 'subject', 'ownerTeacher']),
            'programs' => $this->programs($request),
            'subjects' => $this->subjects($request),
            'ownerTeachers' => $this->ownerTeachers($request),
            'isTeacherScoped' => $request->user()->isTeacher(),
        ]);
    }

    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        DB::transaction(function () use ($request, $batch): void {
            $batch->update($this->validatedPayload($request));
            $this->syncSchedules($batch, $request);
        });

        return redirect()
            ->route('batches.edit', $batch)
            ->with('status', 'Batch updated successfully.');
    }

    public function destroy(Request $request, Batch $batch): RedirectResponse
    {
        $this->authorize('delete', $batch);
        $batch->delete();

        return redirect()
            ->route('batches.index')
            ->with('status', 'Batch deleted successfully.');
    }

    protected function programs(Request $request)
    {
        return Program::query()
            ->forCurrentTenant()
            ->where('status', Program::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    protected function subjects(Request $request)
    {
        return Subject::query()
            ->forCurrentTenant()
            ->where('status', Subject::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    protected function ownerTeachers(Request $request)
    {
        $query = Teacher::query()
            ->forCurrentTenant()
            ->where('status', Teacher::STATUS_ACTIVE)
            ->where('can_own_batches', true);

        if ($request->user()->isTeacher() && $request->user()->teacher) {
            $query->whereKey($request->user()->teacher->getKey());
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedPayload(StoreBatchRequest|UpdateBatchRequest $request): array
    {
        $validated = $request->validated();
        unset(
            $validated['schedule_days'],
            $validated['schedule_start_times'],
            $validated['schedule_end_times'],
            $validated['schedule_rooms'],
        );

        $validated['tenant_id'] = $request->user()->tenant_id;

        if ($request->user()->isTeacher() && $request->user()->teacher) {
            $validated['owner_teacher_id'] = $request->user()->teacher->getKey();
        }

        return $validated;
    }

    protected function syncSchedules(Batch $batch, StoreBatchRequest|UpdateBatchRequest $request): void
    {
        $days = $request->input('schedule_days', []);
        $starts = $request->input('schedule_start_times', []);
        $ends = $request->input('schedule_end_times', []);
        $rooms = $request->input('schedule_rooms', []);

        $rows = collect($days)
            ->map(function ($day, $index) use ($starts, $ends, $rooms): ?array {
                $start = $starts[$index] ?? null;
                $end = $ends[$index] ?? null;
                $room = $rooms[$index] ?? null;

                if (! filled($day) && ! filled($start) && ! filled($end)) {
                    return null;
                }

                return [
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'room_name' => filled($room) ? $room : null,
                    'sort_order' => $index + 1,
                ];
            })
            ->filter()
            ->values();

        $batch->schedules()->delete();

        if ($rows->isNotEmpty()) {
            $batch->schedules()->createMany($rows->all());
        }
    }
}
