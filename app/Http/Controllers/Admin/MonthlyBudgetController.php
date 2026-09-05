<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyBudget;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyBudgetController extends Controller
{
    public function index(): View
    {
        $budgets = MonthlyBudget::with('setter')->orderByDesc('year')->orderByDesc('month')->paginate(24);

        return view('admin.budgets.index', compact('budgets'));
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'amount_bdt' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $budget = MonthlyBudget::updateOrCreate(
            ['year' => $data['year'], 'month' => $data['month']],
            [
                'amount_bdt' => $data['amount_bdt'],
                'set_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]
        );

        $audit->log('monthly_budget.set', $budget, null, $data);

        return back()->with('success', 'Monthly budget saved.');
    }
}
