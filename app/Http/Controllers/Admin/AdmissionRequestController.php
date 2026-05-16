<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveAdmissionRequest;
use App\Http\Requests\Admin\RejectAdmissionRequest;
use App\Models\AdmissionRequest;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentFaceRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionRequestController extends Controller
{
    /**
     * Display a listing of admission requests.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $batchId = $request->integer('batch_id');
        $hasFilters = $search !== '' || in_array($status, ['pending', 'approved', 'rejected'], true) || $batchId > 0;

        $baseQuery = AdmissionRequest::query()
            ->with(['batch.academicClass', 'batch.subject', 'student', 'reviewer'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('guardian_phone', 'like', "%{$search}%")
                        ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->when($batchId > 0, fn ($query) => $query->where('batch_id', $batchId));

        $requests = $hasFilters
            ? $baseQuery->latest()->paginate(12)->withQueryString()
            : AdmissionRequest::query()->whereRaw('1 = 0')->paginate(12);

        return view('admin.admission-requests.index', [
            'requests' => $requests,
            'search' => $search,
            'status' => $status,
            'batchId' => $batchId,
            'hasFilters' => $hasFilters,
            'batches' => \App\Models\Batch::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'totalRequests' => AdmissionRequest::query()->count(),
            'pendingRequests' => AdmissionRequest::query()->where('status', 'pending')->count(),
            'approvedRequests' => AdmissionRequest::query()->where('status', 'approved')->count(),
        ]);
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

            StudentFaceRegistration::query()
                ->where('admission_request_id', $admissionRequest->id)
                ->update([
                    'student_id' => $student->id,
                    'status' => 'verified',
                    'verified_by' => $request->user()->id,
                    'verified_at' => now(),
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

        StudentFaceRegistration::query()
            ->where('admission_request_id', $admissionRequest->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'note' => $request->input('review_note'),
            ]);

        return redirect()
            ->route('admin.admission-requests.show', $admissionRequest)
            ->with('success', 'Admission request rejected.');
    }
}
