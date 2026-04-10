<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceSessionRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\StudentEnrollment;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $sessions = AttendanceSession::query()
            ->with(['batch', 'ownerTeacher', 'takenBy', 'records'])
            ->visibleTo($request->user())
            ->when($request->filled('batch_id'), fn (Builder $query) => $query->where('batch_id', $request->integer('batch_id')))
            ->when($request->filled('attendance_date'), fn (Builder $query) => $query->whereDate('attendance_date', $request->date('attendance_date')?->toDateString()))
            ->latest('attendance_date')
            ->paginate(15)
            ->withQueryString();

        return view('attendance.index', [
            'sessions' => $sessions,
            'batches' => Batch::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceSession::class);

        $batch = $request->filled('batch_id')
            ? Batch::query()->visibleTo($request->user())->findOrFail($request->integer('batch_id'))
            : Batch::query()->visibleTo($request->user())->orderBy('name')->first();
        $attendanceDate = $request->filled('attendance_date')
            ? Carbon::parse((string) $request->input('attendance_date'))
            : now();
        $session = $batch
            ? AttendanceSession::query()
                ->visibleTo($request->user())
                ->where('batch_id', $batch->getKey())
                ->whereDate('attendance_date', $attendanceDate->toDateString())
                ->with('records')
                ->first()
            : null;

        return view('attendance.create', [
            'batches' => Batch::query()->visibleTo($request->user())->orderBy('name')->get(['id', 'name', 'code']),
            'selectedBatch' => $batch,
            'attendanceDate' => $attendanceDate,
            'roster' => $batch ? $this->roster($request->user(), $batch, $session) : collect(),
            'statuses' => AttendanceRecord::statuses(),
            'session' => $session,
        ]);
    }

    public function store(StoreAttendanceSessionRequest $request): RedirectResponse
    {
        $batch = Batch::query()->visibleTo($request->user())->findOrFail($request->integer('batch_id'));

        $session = $this->attendanceService->saveBatchAttendance(
            actor: $request->user(),
            batch: $batch,
            attendanceDate: Carbon::parse($request->validated()['attendance_date']),
            records: $request->validated()['records'],
            notes: $request->validated()['notes'] ?? null,
        );

        return redirect()
            ->route('attendance.create', [
                'batch_id' => $batch->getKey(),
                'attendance_date' => $session->attendance_date->toDateString(),
            ])
            ->with('status', 'Attendance saved successfully.');
    }

    protected function roster($user, Batch $batch, ?AttendanceSession $session)
    {
        $recordMap = $session?->records?->keyBy('student_id') ?? collect();

        return StudentEnrollment::query()
            ->visibleTo($user)
            ->where('batch_id', $batch->getKey())
            ->where('status', StudentEnrollment::STATUS_ACTIVE)
            ->with('student')
            ->orderBy('student_id')
            ->get()
            ->map(function (StudentEnrollment $enrollment) use ($recordMap) {
                $record = $recordMap->get($enrollment->student_id);

                return [
                    'student' => $enrollment->student,
                    'enrollment' => $enrollment,
                    'status' => $record?->status ?? AttendanceRecord::STATUS_PRESENT,
                    'remarks' => $record?->remarks,
                ];
            });
    }
}
