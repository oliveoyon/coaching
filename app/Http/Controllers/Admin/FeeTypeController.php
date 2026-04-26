<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeeTypeRequest;
use App\Models\FeeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeTypeController extends Controller
{
    /**
     * Display a listing of fee types.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $feeTypes = FeeType::query()
            ->withCount('batchFees')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.fee-types.index', compact('feeTypes', 'search'));
    }

    /**
     * Show the form for creating a new fee type.
     */
    public function create(): View
    {
        return view('admin.fee-types.create');
    }

    /**
     * Store a newly created fee type in storage.
     */
    public function store(FeeTypeRequest $request): RedirectResponse
    {
        FeeType::create($request->validated());

        return redirect()
            ->route('admin.fee-types.index')
            ->with('success', 'Fee type created successfully.');
    }

    /**
     * Show the form for editing the specified fee type.
     */
    public function edit(FeeType $feeType): View
    {
        return view('admin.fee-types.edit', compact('feeType'));
    }

    /**
     * Update the specified fee type in storage.
     */
    public function update(FeeTypeRequest $request, FeeType $feeType): RedirectResponse
    {
        $feeType->update($request->validated());

        return redirect()
            ->route('admin.fee-types.index')
            ->with('success', 'Fee type updated successfully.');
    }
}
