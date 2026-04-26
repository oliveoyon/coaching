<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display expense list with filters and monthly summary.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = (string) $request->string('type');
        $month = (string) $request->string('month') ?: now()->format('Y-m');
        $monthStart = $month.'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $expenses = Expense::query()
            ->with(['teacher.user', 'creator'])
            ->when(in_array($type, ['common', 'teacher'], true), fn ($query) => $query->where('type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('note', 'like', "%{$search}%")
                        ->orWhereHas('teacher.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($month !== '', fn ($query) => $query->whereBetween('expense_date', [$monthStart, $monthEnd]))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $monthlySummary = [
            'total' => (float) Expense::query()
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount'),
            'common' => (float) Expense::query()
                ->where('type', 'common')
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount'),
            'teacher' => (float) Expense::query()
                ->where('type', 'teacher')
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount'),
        ];

        return view('admin.expenses.index', compact('expenses', 'search', 'type', 'month', 'monthlySummary'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(): View
    {
        return view('admin.expenses.create', [
            'teachers' => $this->activeTeachers(),
        ]);
    }

    /**
     * Store a newly created expense.
     */
    public function store(ExpenseRequest $request): RedirectResponse
    {
        Expense::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense): View
    {
        return view('admin.expenses.edit', [
            'expense' => $expense,
            'teachers' => $this->activeTeachers($expense),
        ]);
    }

    /**
     * Update the specified expense.
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Build active teacher options.
     */
    protected function activeTeachers(?Expense $expense = null)
    {
        return Teacher::query()
            ->with('user')
            ->when($expense?->teacher_id, function ($query) use ($expense) {
                $query->where('teachers.status', 'active')
                    ->orWhere('teachers.id', $expense->teacher_id);
            }, function ($query) {
                $query->where('teachers.status', 'active');
            })
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->orderBy('users.name')
            ->select('teachers.*')
            ->get();
    }
}
