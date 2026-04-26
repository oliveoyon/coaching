<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherSettlementRequest;
use App\Models\Distribution;
use App\Models\Teacher;
use App\Models\TeacherSettlement;
use App\Services\TeacherSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TeacherSettlementController extends Controller
{
    /**
     * Display settlement history and payable summary.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('settle teacher payments') || $request->user()?->hasRole('Teacher'), Response::HTTP_FORBIDDEN);

        $search = trim((string) $request->string('search'));
        $month = (string) $request->string('month') ?: now()->format('Y-m');
        $teacherId = $request->integer('teacher_id');
        $monthStart = $month.'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $summaryRows = Teacher::query()
            ->with('user')
            ->where('teachers.status', 'active')
            ->when($request->user()->hasRole('Teacher') && ! $request->user()->can('settle teacher payments'), function ($query) use ($request) {
                $teacher = $request->user()->teacherProfile;

                abort_if(! $teacher, Response::HTTP_FORBIDDEN);

                $query->where('teachers.id', $teacher->id);
            })
            ->when($teacherId, fn ($query) => $query->where('teachers.id', $teacherId))
            ->when($search !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")))
            ->get()
            ->map(function (Teacher $teacher) use ($monthStart, $monthEnd) {
                $earned = (float) Distribution::query()
                    ->where('teacher_id', $teacher->id)
                    ->sum('amount');

                $earnedThisMonth = (float) Distribution::query()
                    ->where('teacher_id', $teacher->id)
                    ->whereHas('payment', fn ($query) => $query->whereBetween('payment_date', [$monthStart, $monthEnd]))
                    ->sum('amount');

                $settled = (float) \App\Models\TeacherSettlementItem::query()
                    ->whereHas('distribution', fn ($query) => $query->where('teacher_id', $teacher->id))
                    ->sum('amount');

                $settledThisMonth = (float) TeacherSettlement::query()
                    ->where('teacher_id', $teacher->id)
                    ->whereBetween('settlement_date', [$monthStart, $monthEnd])
                    ->sum('amount');

                return [
                    'teacher' => $teacher,
                    'earned' => $earned,
                    'earned_this_month' => $earnedThisMonth,
                    'settled' => $settled,
                    'settled_this_month' => $settledThisMonth,
                    'outstanding' => max(0, $earned - $settled),
                ];
            })
            ->filter(function ($row) use ($request) {
                return ! ($request->user()->hasRole('Teacher') && ! $request->user()->can('settle teacher payments')) || $row['teacher']->id === $request->user()->teacherProfile?->id;
            })
            ->values();

        $settlements = TeacherSettlement::query()
            ->with(['teacher.user', 'payer', 'items.distribution.payment.collector'])
            ->when($request->user()->hasRole('Teacher') && ! $request->user()->can('settle teacher payments'), function ($query) use ($request) {
                $teacher = $request->user()->teacherProfile;

                abort_if(! $teacher, Response::HTTP_FORBIDDEN);

                $query->where('teacher_id', $teacher->id);
            })
            ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
            ->when($search !== '', fn ($query) => $query->whereHas('teacher.user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")))
            ->whereBetween('settlement_date', [$monthStart, $monthEnd])
            ->latest('settlement_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $teachers = Teacher::query()
            ->with('user')
            ->where('teachers.status', 'active')
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->orderBy('users.name')
            ->select('teachers.*')
            ->get();

        $routeName = $request->user()->hasRole('Teacher') && ! $request->user()->can('settle teacher payments')
            ? 'teacher.settlements.index'
            : 'admin.teacher-settlements.index';

        return view('admin.teacher-settlements.index', compact(
            'summaryRows',
            'settlements',
            'teachers',
            'search',
            'month',
            'teacherId',
            'routeName',
        ));
    }

    /**
     * Show settlement creation form.
     */
    public function create(): View
    {
        return view('admin.teacher-settlements.create', [
            'teachers' => Teacher::query()
                ->with('user')
                ->where('teachers.status', 'active')
                ->whereHas('user', fn ($query) => $query->where('status', 'active'))
                ->join('users', 'users.id', '=', 'teachers.user_id')
                ->orderBy('users.name')
                ->select('teachers.*')
                ->get(),
        ]);
    }

    /**
     * Store a newly created teacher settlement.
     */
    public function store(TeacherSettlementRequest $request, TeacherSettlementService $settlementService): RedirectResponse
    {
        $teacher = Teacher::query()->findOrFail($request->integer('teacher_id'));

        $settlementService->settle(
            $teacher,
            (float) $request->input('amount'),
            $request->date('settlement_date')->format('Y-m-d'),
            $request->user()->id,
            $request->string('note')->toString() ?: null,
        );

        return redirect()
            ->route('admin.teacher-settlements.index')
            ->with('success', 'Teacher settlement recorded successfully.');
    }
}
