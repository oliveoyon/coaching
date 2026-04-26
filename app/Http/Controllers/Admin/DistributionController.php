<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DistributionController extends Controller
{
    /**
     * Display distribution history for admins/accounts or own earnings for teachers.
     */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('approve payments') || $request->user()?->can('settle teacher payments') || $request->user()?->hasRole('Teacher'),
            Response::HTTP_FORBIDDEN
        );

        $search = trim((string) $request->string('search'));
        $month = (string) $request->string('month');

        $query = Distribution::query()
            ->with([
                'teacher.user',
                'payment.enrollment.student',
                'payment.enrollment.batch.academicClass',
                'payment.enrollment.batch.subject',
                'payment.batchFee.feeType',
                'payment.collector',
                'settlementItems',
            ]);

        if ($request->user()->hasRole('Teacher') && ! $request->user()->can('approve payments') && ! $request->user()->can('settle teacher payments')) {
            $teacher = $request->user()->teacherProfile;

            abort_if(! $teacher, Response::HTTP_FORBIDDEN);

            $query->where('teacher_id', $teacher->id);
        }

        $distributions = $query
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->whereHas('teacher.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('payment.enrollment.student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('student_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('guardian_phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('payment.enrollment.batch', fn ($batchQuery) => $batchQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($month !== '', fn ($builder) => $builder->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('month', $month)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = clone $query;

        $summary = [
            'overall' => (float) $summaryQuery->sum('amount'),
            'month' => 0.0,
            'count' => (clone $query)->count(),
            'settled' => (float) \App\Models\TeacherSettlementItem::query()
                ->when($request->user()->hasRole('Teacher') && ! $request->user()->can('approve payments') && ! $request->user()->can('settle teacher payments'), function ($builder) use ($request) {
                    $teacher = $request->user()->teacherProfile;

                    if ($teacher) {
                        $builder->whereHas('distribution', fn ($query) => $query->where('teacher_id', $teacher->id));
                    }
                })
                ->sum('amount'),
        ];
        $summary['outstanding'] = max(0, $summary['overall'] - $summary['settled']);

        $currentMonth = now()->format('Y-m');
        $summary['month'] = (float) Distribution::query()
            ->when($request->user()->hasRole('Teacher') && ! $request->user()->can('approve payments') && ! $request->user()->can('settle teacher payments'), function ($builder) use ($request) {
                $teacher = $request->user()->teacherProfile;

                if ($teacher) {
                    $builder->where('teacher_id', $teacher->id);
                }
            })
            ->whereHas('payment', fn ($paymentQuery) => $paymentQuery->whereBetween('payment_date', [$currentMonth.'-01', date('Y-m-t', strtotime($currentMonth.'-01'))]))
            ->sum('amount');

        $pageTitle = $request->user()->hasRole('Teacher') && ! $request->user()->can('approve payments') && ! $request->user()->can('settle teacher payments')
            ? 'My Earnings'
            : 'Distribution History';

        $routeName = $request->user()->hasRole('Teacher') && ! $request->user()->can('approve payments') && ! $request->user()->can('settle teacher payments')
            ? 'teacher.earnings.index'
            : 'admin.distributions.index';

        return view('admin.distributions.index', compact('distributions', 'search', 'month', 'pageTitle', 'routeName', 'summary', 'currentMonth'));
    }
}
