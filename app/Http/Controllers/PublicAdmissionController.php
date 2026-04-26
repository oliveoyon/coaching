<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicAdmissionRequest;
use App\Models\AdmissionRequest as AdmissionRequestModel;
use App\Models\BatchAdmissionLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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

        $photoPath = $request->file('photo')->store('admission-requests/photos', 'public');

        AdmissionRequestModel::create([
            'batch_admission_link_id' => $link->id,
            'batch_id' => $link->batch_id,
            'name' => $request->string('name')->toString(),
            'phone' => $request->string('phone')->toString() ?: null,
            'guardian_phone' => $request->string('guardian_phone')->toString(),
            'school' => $request->string('school')->toString() ?: null,
            'address' => $request->string('address')->toString() ?: null,
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]);

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
}
