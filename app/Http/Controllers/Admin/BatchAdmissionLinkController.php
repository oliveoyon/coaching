<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBatchAdmissionLinkRequest;
use App\Models\Batch;
use App\Models\BatchAdmissionLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class BatchAdmissionLinkController extends Controller
{
    /**
     * Display a listing of admission links.
     */
    public function index(): View
    {
        $links = BatchAdmissionLink::query()
            ->with(['batch.academicClass', 'batch.subject', 'creator'])
            ->withCount(['admissionRequests', 'admissionRequests as pending_requests_count' => fn ($query) => $query->where('status', 'pending')])
            ->latest()
            ->paginate(12);

        return view('admin.admission-links.index', compact('links'));
    }

    /**
     * Show the form for creating a new admission link.
     */
    public function create(): View
    {
        $batches = Batch::query()
            ->with(['academicClass', 'subject'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedBatchId = request()->integer('batch_id') ?: null;

        return view('admin.admission-links.create', compact('batches', 'selectedBatchId'));
    }

    /**
     * Store a newly created admission link in storage.
     */
    public function store(StoreBatchAdmissionLinkRequest $request): RedirectResponse
    {
        $link = BatchAdmissionLink::create([
            'batch_id' => $request->integer('batch_id'),
            'title' => $request->string('title')->toString() ?: null,
            'token' => Str::random(48),
            'status' => 'active',
            'expires_at' => $request->date('expires_at')?->format('Y-m-d H:i:s'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.admission-links.show', $link)
            ->with('success', 'Batch admission link created successfully.');
    }

    /**
     * Display the specified admission link.
     */
    public function show(BatchAdmissionLink $admissionLink): View
    {
        $admissionLink->load([
            'batch.academicClass',
            'batch.subject',
            'batch.teachers.user',
            'creator',
            'admissionRequests' => fn ($query) => $query->latest()->limit(10),
        ]);

        return view('admin.admission-links.show', compact('admissionLink'));
    }
}
