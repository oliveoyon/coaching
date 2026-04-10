<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentFeeOverrideRequest;
use App\Http\Requests\UpdateStudentFeeOverrideRequest;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFeeOverride;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentFeeOverrideController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentFeeOverride::class);

        $overrides = StudentFeeOverride::query()
            ->forCurrentTenant()
            ->with(['student', 'feeStructure.feeHead'])
            ->latest()
            ->paginate(12);

        return view('student-fee-overrides.index', [
            'overrides' => $overrides,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StudentFeeOverride::class);

        return view('student-fee-overrides.create', [
            'override' => new StudentFeeOverride([
                'is_active' => true,
            ]),
            'students' => $this->students(),
            'feeStructures' => $this->feeStructures(),
        ]);
    }

    public function store(StoreStudentFeeOverrideRequest $request): RedirectResponse
    {
        $override = StudentFeeOverride::updateOrCreate(
            [
                'tenant_id' => $request->user()->tenant_id,
                'student_id' => $request->integer('student_id'),
                'fee_structure_id' => $request->integer('fee_structure_id'),
            ],
            array_merge($request->validated(), [
                'tenant_id' => $request->user()->tenant_id,
                'is_active' => (bool) ($request->validated()['is_active'] ?? false),
            ])
        );

        return redirect()
            ->route('student-fee-overrides.edit', $override)
            ->with('status', 'Student fee override saved successfully.');
    }

    public function edit(StudentFeeOverride $studentFeeOverride): View
    {
        $this->authorize('update', $studentFeeOverride);

        return view('student-fee-overrides.edit', [
            'override' => $studentFeeOverride->load(['student', 'feeStructure.feeHead']),
            'students' => $this->students(),
            'feeStructures' => $this->feeStructures(),
        ]);
    }

    public function update(UpdateStudentFeeOverrideRequest $request, StudentFeeOverride $studentFeeOverride): RedirectResponse
    {
        $studentFeeOverride->update(array_merge($request->validated(), [
            'tenant_id' => $request->user()->tenant_id,
            'is_active' => (bool) ($request->validated()['is_active'] ?? false),
        ]));

        return redirect()
            ->route('student-fee-overrides.edit', $studentFeeOverride)
            ->with('status', 'Student fee override updated successfully.');
    }

    public function destroy(StudentFeeOverride $studentFeeOverride): RedirectResponse
    {
        $this->authorize('delete', $studentFeeOverride);

        $studentFeeOverride->delete();

        return redirect()
            ->route('student-fee-overrides.index')
            ->with('status', 'Student fee override deleted successfully.');
    }

    protected function students()
    {
        return Student::query()
            ->forCurrentTenant()
            ->orderBy('name')
            ->get(['id', 'name', 'student_code']);
    }

    protected function feeStructures()
    {
        return FeeStructure::query()
            ->forCurrentTenant()
            ->with('feeHead')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }
}
