<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\AcademicClass;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $students = Student::query()
            ->with('academicClass')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('student_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('guardian_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): View
    {
        abort_unless(auth()->user()?->can('manage students'), Response::HTTP_FORBIDDEN);

        return view('admin.students.create', [
            'classes' => $this->formClasses(),
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StudentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }

        unset($validated['photo']);

        Student::create($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student admitted successfully.');
    }

    /**
     * Display the specified student profile.
     */
    public function show(Student $student): View
    {
        $request = request();
        abort_unless($this->canAccessStudentProfile($request, $student), Response::HTTP_FORBIDDEN);

        $month = (string) $request->string('month') ?: now()->format('Y-m');
        $student->load('academicClass');

        $enrollments = $this->scopedEnrollmentsForProfile($request, $student)
            ->with([
                'batch.academicClass',
                'batch.subject',
                'batch.teachers.user',
                'batch.batchFees.feeType',
                'payments.batchFee.feeType',
            ])
            ->latest('start_date')
            ->get();

        $student->load([
            'admissionRequests' => fn ($query) => $query->with(['batch', 'reviewer'])->latest(),
        ]);

        $paymentHistory = $student->payments()
            ->with(['enrollment.batch', 'batchFee.feeType', 'collector', 'approver'])
            ->when($request->user()->hasRole('Teacher'), function ($query) use ($request) {
                $teacher = $request->user()->teacherProfile;

                if ($teacher) {
                    $query->whereHas('enrollment.batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
                }
            })
            ->latest('payment_date')
            ->latest('id')
            ->limit(50)
            ->get();

        $paymentGroups = $paymentHistory
            ->groupBy(fn ($payment) => $payment->enrollment?->batch?->name ?: 'Other')
            ->map(function (Collection $payments, string $batchName) {
                return [
                    'batch_name' => $batchName,
                    'approved' => (float) $payments->where('status', 'approved')->sum('amount'),
                    'pending' => (float) $payments->where('status', 'pending')->sum('amount'),
                    'count' => $payments->count(),
                    'last_payment_date' => $payments->first()?->payment_date,
                ];
            })
            ->values();

        $feeSummaryRows = $this->buildStudentFeeSummaryRows($enrollments->where('status', 'active'), $month);

        $profileSummary = [
            'active_enrollments' => $enrollments->where('status', 'active')->count(),
            'withdrawn_enrollments' => $enrollments->where('status', 'withdrawn')->count(),
            'total_batches' => $enrollments->pluck('batch_id')->unique()->count(),
            'first_joined_at' => $enrollments->min('start_date'),
            'month_approved' => (float) $paymentHistory
                ->where('status', 'approved')
                ->filter(fn ($payment) => $payment->month === $month || ($payment->month === null && $payment->payment_date?->format('Y-m') === $month))
                ->sum('amount'),
            'month_pending' => (float) $paymentHistory
                ->where('status', 'pending')
                ->filter(fn ($payment) => $payment->month === $month || ($payment->month === null && $payment->payment_date?->format('Y-m') === $month))
                ->sum('amount'),
            'month_due' => (float) $feeSummaryRows->sum('remaining'),
            'total_approved' => (float) $paymentHistory->where('status', 'approved')->sum('amount'),
            'total_pending' => (float) $paymentHistory->where('status', 'pending')->sum('amount'),
            'payment_count' => $paymentHistory->count(),
        ];

        return view('admin.students.show', compact('student', 'enrollments', 'paymentHistory', 'paymentGroups', 'feeSummaryRows', 'profileSummary', 'month'));
    }

    /**
     * Search students quickly and jump to profile.
     */
    public function lookup(Request $request)
    {
        $search = trim((string) $request->string('student_search'));
        abort_unless($request->user()?->hasRole('Teacher') || $request->user()?->canany(['manage students', 'manage enrollments', 'collect payments']), Response::HTTP_FORBIDDEN);

        $students = Student::query()
            ->with('academicClass')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('student_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('guardian_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->user()->hasRole('Teacher'), function ($query) use ($request) {
                $teacher = $request->user()->teacherProfile;

                abort_if(! $teacher, Response::HTTP_FORBIDDEN);

                $query->whereHas('enrollments.batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
            })
            ->limit(20)
            ->get();

        if ($search !== '' && $students->count() === 1) {
            return redirect()->route('admin.student-profiles.show', ['student' => $students->first()->id, 'month' => now()->format('Y-m')]);
        }

        return view('admin.students.lookup', compact('students', 'search'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): View
    {
        abort_unless(auth()->user()?->can('manage students'), Response::HTTP_FORBIDDEN);

        return view('admin.students.edit', [
            'student' => $student,
            'classes' => $this->formClasses($student),
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }

            $validated['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }

        unset($validated['photo']);

        $student->update($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Build class options for the student form.
     */
    protected function formClasses(?Student $student = null)
    {
        return AcademicClass::query()
            ->when($student?->class_id, function ($query) use ($student) {
                $query->where('status', 'active')
                    ->orWhere('id', $student->class_id);
            }, function ($query) {
                $query->where('status', 'active');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get the enrollments visible in the profile page.
     */
    protected function scopedEnrollmentsForProfile(Request $request, Student $student)
    {
        return $student->enrollments()
            ->when($request->user()->hasRole('Teacher') && ! $request->user()->can('manage enrollments'), function ($query) use ($request) {
                $teacher = $request->user()?->teacherProfile;

                if ($teacher) {
                    $query->whereHas('batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id));
                }
            });
    }

    /**
     * Build fee summary rows for the student profile.
     */
    protected function buildStudentFeeSummaryRows(Collection $enrollments, string $month): Collection
    {
        return $enrollments
            ->flatMap(function ($enrollment) use ($month) {
                return $enrollment->batch?->batchFees?->where('status', 'active')
                    ->map(function ($batchFee) use ($enrollment, $month) {
                        $billingMonth = $batchFee->feeType?->frequency === 'monthly' ? $month : null;

                        return [
                            'enrollment' => $enrollment,
                            'batch_name' => $enrollment->batch?->name,
                            'fee_name' => $batchFee->feeType?->name,
                            'frequency' => $batchFee->feeType?->frequency,
                            'amount' => (float) $batchFee->amount,
                            'approved' => $enrollment->approvedPaidForFee($batchFee, $billingMonth),
                            'pending' => $enrollment->pendingPaidForFee($batchFee, $billingMonth),
                            'remaining' => $enrollment->remainingForFee($batchFee, $billingMonth),
                        ];
                    }) ?? collect();
            })
            ->sortBy([
                ['batch_name', 'asc'],
                ['fee_name', 'asc'],
            ])
            ->values();
    }

    /**
     * Determine whether the authenticated user can access the student profile.
     */
    protected function canAccessStudentProfile(Request $request, Student $student): bool
    {
        if ($request->user()?->canany(['manage students', 'manage enrollments'])) {
            return true;
        }

        if (! $request->user()?->can('collect payments')) {
            return false;
        }

        if (! $request->user()->hasRole('Teacher')) {
            return true;
        }

        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return false;
        }

        return $student->enrollments()
            ->whereHas('batch.teachers', fn ($teacherQuery) => $teacherQuery->where('teachers.id', $teacher->id))
            ->exists();
    }
}
