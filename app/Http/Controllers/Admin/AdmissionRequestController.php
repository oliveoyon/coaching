<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveAdmissionRequest;
use App\Http\Requests\Admin\RejectAdmissionRequest;
use App\Models\AdmissionRequest;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdmissionRequestController extends Controller
{
    /**
     * Display a listing of admission requests.
     */
    public function index(): View
    {
        $requests = AdmissionRequest::query()
            ->with(['batch.academicClass', 'batch.subject', 'student', 'reviewer'])
            ->latest()
            ->paginate(12);

        return view('admin.admission-requests.index', compact('requests'));
    }

    /**
     * Display the specified admission request.
     */
    public function show(AdmissionRequest $admissionRequest): View
    {
        $admissionRequest->load([
            'batch.academicClass',
            'batch.subject',
            'batch.teachers.user',
            'batchAdmissionLink',
            'student',
            'reviewer',
        ]);

        $possibleMatches = Student::query()
            ->with('academicClass')
            ->where(function ($query) use ($admissionRequest) {
                if ($admissionRequest->phone) {
                    $query->where('phone', $admissionRequest->phone);
                }

                $query->orWhere('guardian_phone', $admissionRequest->guardian_phone)
                    ->orWhere('name', $admissionRequest->name);
            })
            ->orderBy('name')
            ->get();

        return view('admin.admission-requests.show', compact('admissionRequest', 'possibleMatches'));
    }

    /**
     * Approve an admission request and enroll the student.
     */
    public function approve(ApproveAdmissionRequest $request, AdmissionRequest $admissionRequest): RedirectResponse
    {
        abort_if($admissionRequest->status !== 'pending', 422);

        DB::transaction(function () use ($request, $admissionRequest): void {
            $student = $request->filled('existing_student_id')
                ? Student::query()->findOrFail($request->integer('existing_student_id'))
                : Student::create([
                    'name' => $admissionRequest->name,
                    'class_id' => $admissionRequest->batch->class_id,
                    'phone' => $admissionRequest->phone,
                    'guardian_phone' => $admissionRequest->guardian_phone,
                    'school' => $admissionRequest->school,
                    'address' => $admissionRequest->address,
                    'photo_path' => $admissionRequest->photo_path,
                    'status' => 'active',
                ]);

            if ($request->filled('existing_student_id')) {
                $fill = [];

                if ($student->status !== 'active') {
                    $fill['status'] = 'active';
                }

                if (! $student->photo_path && $admissionRequest->photo_path) {
                    $fill['photo_path'] = $admissionRequest->photo_path;
                }

                if (! $student->phone && $admissionRequest->phone) {
                    $fill['phone'] = $admissionRequest->phone;
                }

                if (! $student->school && $admissionRequest->school) {
                    $fill['school'] = $admissionRequest->school;
                }

                if (! $student->address && $admissionRequest->address) {
                    $fill['address'] = $admissionRequest->address;
                }

                if ($fill !== []) {
                    $student->update($fill);
                }
            }

            Enrollment::create([
                'student_id' => $student->id,
                'batch_id' => $admissionRequest->batch_id,
                'start_date' => $request->date('start_date')->format('Y-m-d'),
                'end_date' => null,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            $admissionRequest->update([
                'student_id' => $student->id,
                'status' => 'approved',
                'review_note' => $request->input('review_note'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.admission-requests.show', $admissionRequest)
            ->with('success', 'Admission request approved and student enrolled successfully.');
    }

    /**
     * Reject an admission request.
     */
    public function reject(RejectAdmissionRequest $request, AdmissionRequest $admissionRequest): RedirectResponse
    {
        abort_if($admissionRequest->status !== 'pending', 422);

        $admissionRequest->update([
            'status' => 'rejected',
            'review_note' => $request->input('review_note'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.admission-requests.show', $admissionRequest)
            ->with('success', 'Admission request rejected.');
    }
}
