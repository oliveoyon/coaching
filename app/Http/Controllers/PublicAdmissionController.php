<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicAdmissionRequest;
use App\Models\AdmissionRequest as AdmissionRequestModel;
use App\Models\BatchAdmissionLink;
use App\Models\StudentFaceRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicAdmissionController extends Controller
{
    /**
     * Show the public batch admission form.
     */
    public function create(string $token): View
    {
        $link = $this->findOpenLink($token);
        $link->load(['batch.academicClass', 'batch.subject', 'batch.teachers.user']);

        return view('admission.apply', compact('link'));
    }

    /**
     * Store a public batch admission request.
     */
    public function store(PublicAdmissionRequest $request, string $token): RedirectResponse
    {
        $link = $this->findOpenLink($token);

        DB::transaction(function () use ($request, $link): void {
            $capturePath = $this->storeFaceCapture($request);

            $admissionRequest = AdmissionRequestModel::create([
                'batch_admission_link_id' => $link->id,
                'batch_id' => $link->batch_id,
                'name' => $request->string('name')->toString(),
                'phone' => $request->string('phone')->toString() ?: null,
                'guardian_phone' => $request->string('guardian_phone')->toString(),
                'school' => $request->string('school')->toString() ?: null,
                'address' => $request->string('address')->toString() ?: null,
                'photo_path' => $capturePath,
                'status' => 'pending',
            ]);

            StudentFaceRegistration::create([
                'admission_request_id' => $admissionRequest->id,
                'capture_path' => $capturePath,
                'capture_method' => $request->filled('face_capture') ? 'live_camera' : 'file_upload',
                'status' => 'pending',
                'captured_at' => now(),
                'note' => 'Captured from the public admission link for later face attendance verification.',
            ]);
        });

        return redirect()
            ->route('admission.apply', $link->token)
            ->with('success', 'Your request has been submitted successfully. Admin will verify and enroll you.');
    }

    /**
     * Find an admission link that is currently open.
     */
    protected function findOpenLink(string $token): BatchAdmissionLink
    {
        $link = BatchAdmissionLink::query()
            ->with('batch')
            ->where('token', $token)
            ->firstOrFail();

        abort_unless($link->isOpen() && $link->batch?->status === 'active', Response::HTTP_NOT_FOUND);

        return $link;
    }

    /**
     * Store either a live face capture or fallback file upload.
     */
    protected function storeFaceCapture(PublicAdmissionRequest $request): string
    {
        if ($request->filled('face_capture')) {
            $base64 = (string) $request->string('face_capture');

            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches) !== 1) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid face capture payload.');
            }

            $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $binary = base64_decode(substr($base64, strpos($base64, ',') + 1), true);

            abort_if($binary === false, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid face capture image.');

            $path = 'admission-requests/faces/'.Str::uuid().'.'.$extension;
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        return $request->file('photo')->store('admission-requests/faces', 'public');
    }
}
