<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttendanceRecordRequest;
use App\Http\Requests\Admin\AttendanceScanRequest;
use App\Http\Requests\Admin\StartAttendanceSessionRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Services\AttendanceSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceSessionService $attendanceSessions)
    {
    }

    /**
     * Display attendance sessions visible to the current user.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->string('status');
        $batchId = $request->integer('batch_id');
        $attendanceDate = (string) $request->string('attendance_date');

        $sessions = $this->accessibleSessionsQuery($request)
            ->with(['batch.academicClass', 'batch.subject', 'batch.teachers.user', 'creator'])
            ->withCount('records')
            ->when(in_array($status, ['in_progress', 'completed'], true), fn ($query) => $query->where('status', $status))
            ->when($batchId > 0, fn ($query) => $query->where('batch_id', $batchId))
            ->when($attendanceDate !== '', fn ($query) => $query->whereDate('attendance_date', $attendanceDate))
            ->latest('attendance_date')
            ->paginate(12)
            ->withQueryString();

        return view('admin.attendance.index', [
            'sessions' => $sessions,
            'status' => $status,
            'batchId' => $batchId,
            'attendanceDate' => $attendanceDate,
            'batches' => $this->formBatches($request),
        ]);
    }

    /**
     * Show the form for starting attendance.
     */
    public function create(Request $request): View
    {
        return view('admin.attendance.create', [
            'batches' => $this->formBatches($request),
            'defaultDate' => now()->toDateString(),
            'defaultMode' => 'face',
        ]);
    }

    /**
     * Open or reuse one attendance session for a batch/day.
     */
    public function store(StartAttendanceSessionRequest $request): RedirectResponse
    {
        $batch = $this->findAccessibleBatch($request, $request->integer('batch_id'));

        $session = $this->attendanceSessions->open(
            $batch,
            $request->date('attendance_date')->format('Y-m-d'),
            $request->user()->id,
            (string) $request->string('mode')
        );

        return redirect()
            ->route('admin.attendance.show', ['attendance' => $session, 'mode' => $session->mode])
            ->with('success', 'Attendance workspace is ready. Students are loaded so nobody gets missed.');
    }

    /**
     * Display the attendance workspace.
     */
    public function show(Request $request, AttendanceSession $attendance): View
    {
        abort_unless($this->canAccessSession($request, $attendance), Response::HTTP_FORBIDDEN);

        $selectedMode = in_array((string) $request->string('mode'), ['manual', 'qr', 'face'], true)
            ? (string) $request->string('mode')
            : (in_array($attendance->mode, ['manual', 'qr', 'face'], true) ? $attendance->mode : 'manual');

        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');

        $attendance->load(['batch.academicClass', 'batch.subject', 'batch.teachers.user', 'creator']);

        $records = $attendance->records()
            ->with([
                'student.faceRegistrations' => fn ($query) => $query->latest(),
                'enrollment',
                'marker',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery
                        ->where('student_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('guardian_phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['pending', 'present', 'absent', 'late', 'excused'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('students.name')
            ->join('students', 'students.id', '=', 'attendance_records.student_id')
            ->select('attendance_records.*')
            ->get();

        $summary = $attendance->records()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'records' => $records,
            'selectedMode' => $selectedMode,
            'search' => $search,
            'status' => $status,
            'summary' => [
                'total' => (int) $attendance->records()->count(),
                'pending' => (int) ($summary['pending'] ?? 0),
                'present' => (int) ($summary['present'] ?? 0),
                'late' => (int) ($summary['late'] ?? 0),
                'excused' => (int) ($summary['excused'] ?? 0),
                'absent' => (int) ($summary['absent'] ?? 0),
            ],
            'scanStatusOptions' => [
                'present' => 'Present',
                'late' => 'Late',
                'excused' => 'Excused',
            ],
        ]);
    }

    /**
     * Update one student row in the session.
     */
    public function mark(AttendanceRecordRequest $request, AttendanceSession $attendance, AttendanceRecord $record): RedirectResponse|JsonResponse
    {
        abort_unless($this->canAccessSession($request, $attendance), Response::HTTP_FORBIDDEN);
        abort_unless($record->attendance_session_id === $attendance->id, Response::HTTP_NOT_FOUND);

        $record->update([
            'status' => $request->input('status'),
            'method' => $request->input('method'),
            'note' => $request->input('note'),
            'confidence_score' => $request->input('confidence_score'),
            'marked_by' => $request->user()->id,
            'marked_at' => now(),
        ]);

        $this->syncSessionMode($attendance, $request->input('method'));

        if ($request->expectsJson()) {
            return response()->json($this->recordPayload($attendance, $record->fresh('marker')));
        }

        return back()->with('success', $record->student?->name.' marked as '.ucfirst((string) $request->input('status')).'.');
    }

    /**
     * Mark attendance from a scanned or typed student code.
     */
    public function scan(AttendanceScanRequest $request, AttendanceSession $attendance): RedirectResponse|JsonResponse
    {
        abort_unless($this->canAccessSession($request, $attendance), Response::HTTP_FORBIDDEN);

        $scanCode = trim((string) $request->string('scan_code'));

        $record = $attendance->records()
            ->whereHas('student', function ($studentQuery) use ($scanCode) {
                $studentQuery
                    ->where('student_code', $scanCode)
                    ->orWhere('phone', $scanCode)
                    ->orWhere('guardian_phone', $scanCode);
            })
            ->with('student')
            ->first();

        if (! $record) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No visible student matched that code or phone number for this batch.',
                ], 404);
            }

            return back()->with('error', 'No visible student matched that code or phone number for this batch.');
        }

        $record->update([
            'status' => $request->input('status'),
            'method' => 'qr',
            'scan_code' => $scanCode,
            'marked_by' => $request->user()->id,
            'marked_at' => now(),
        ]);

        $this->syncSessionMode($attendance, 'qr');

        if ($request->expectsJson()) {
            return response()->json($this->recordPayload($attendance, $record->fresh('marker')));
        }

        return back()->with('success', $record->student?->name.' was marked by code scan.');
    }

    /**
     * Finish the session and mark remaining pending students absent.
     */
    public function complete(Request $request, AttendanceSession $attendance): RedirectResponse|JsonResponse
    {
        abort_unless($this->canAccessSession($request, $attendance), Response::HTTP_FORBIDDEN);

        $attendance->records()
            ->where('status', 'pending')
            ->update([
                'status' => 'absent',
                'method' => 'manual',
                'marked_by' => $request->user()->id,
                'marked_at' => now(),
                'note' => 'Auto-marked absent when the session was completed.',
            ]);

        $attendance->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Attendance session completed. Remaining pending students were marked absent.',
                'summary' => $this->summaryPayload($attendance->fresh()),
            ]);
        }

        return back()->with('success', 'Attendance session completed. Remaining pending students were marked absent.');
    }

    /**
     * Build the visible session query for the current user.
     */
    protected function accessibleSessionsQuery(Request $request)
    {
        $query = AttendanceSession::query();

        if ($this->isAttendanceAdmin($request)) {
            return $query;
        }

        $teacher = $request->user()->teacherProfile;

        abort_if(! $teacher, Response::HTTP_FORBIDDEN);

        return $query->whereHas('batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
    }

    /**
     * Get batches visible in attendance forms.
     */
    protected function formBatches(Request $request): Collection
    {
        $query = Batch::query()
            ->with(['academicClass', 'subject', 'teachers.user'])
            ->where('status', 'active')
            ->orderBy('name');

        if ($this->isAttendanceAdmin($request)) {
            return $query->get();
        }

        $teacher = $request->user()->teacherProfile;

        abort_if(! $teacher, Response::HTTP_FORBIDDEN);

        return $query
            ->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id))
            ->get();
    }

    /**
     * Find one visible batch by ID.
     */
    protected function findAccessibleBatch(Request $request, int $batchId): Batch
    {
        $batch = $this->formBatches($request)->firstWhere('id', $batchId);

        abort_if(! $batch, Response::HTTP_FORBIDDEN);

        return $batch;
    }

    /**
     * Check whether the user can access the session.
     */
    protected function canAccessSession(Request $request, AttendanceSession $attendance): bool
    {
        if ($this->isAttendanceAdmin($request)) {
            return true;
        }

        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return false;
        }

        return $attendance->batch()
            ->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id))
            ->exists();
    }

    /**
     * Keep the session mode honest when multiple marking methods are used.
     */
    protected function syncSessionMode(AttendanceSession $attendance, string $usedMethod): void
    {
        if ($attendance->mode !== $usedMethod) {
            $attendance->update(['mode' => 'mixed']);
        }
    }

    /**
     * Determine whether the current user should see all attendance data.
     */
    protected function isAttendanceAdmin(Request $request): bool
    {
        return $request->user()?->hasAnyRole(['Super Admin', 'Admin', 'Accounts']) ?? false;
    }

    /**
     * Build a lightweight JSON payload for one marked row.
     */
    protected function recordPayload(AttendanceSession $attendance, AttendanceRecord $record): array
    {
        return [
            'message' => $record->student?->name.' marked as '.ucfirst((string) $record->status).'.',
            'record' => [
                'id' => $record->id,
                'status' => $record->status,
                'method' => $record->method,
                'marked_by' => $record->marker?->name,
                'marked_at' => $record->marked_at?->format('d M h:i A'),
            ],
            'summary' => $this->summaryPayload($attendance->fresh()),
        ];
    }

    /**
     * Build updated summary counts for the workspace header.
     */
    protected function summaryPayload(AttendanceSession $attendance): array
    {
        $summary = $attendance->records()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $attendance->records()->count(),
            'pending' => (int) ($summary['pending'] ?? 0),
            'present' => (int) ($summary['present'] ?? 0),
            'late' => (int) ($summary['late'] ?? 0),
            'excused' => (int) ($summary['excused'] ?? 0),
            'absent' => (int) ($summary['absent'] ?? 0),
        ];
    }
}
