<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcademicClassRequest;
use App\Models\AcademicClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $classes = AcademicClass::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.classes.index', compact('classes', 'search'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create(): View
    {
        return view('admin.classes.create');
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(AcademicClassRequest $request): RedirectResponse
    {
        AcademicClass::create($request->validated());

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(AcademicClass $class): View
    {
        return view('admin.classes.edit', compact('class'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(AcademicClassRequest $request, AcademicClass $class): RedirectResponse
    {
        $class->update($request->validated());

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'Class updated successfully.');
    }
}
