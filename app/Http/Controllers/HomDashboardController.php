<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomDashboardController extends Controller
{
    public function __invoke(Request $request, BudgetService $budgetService): View
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $backdatedOnly = $request->has('backdated') ? $request->boolean('backdated') : null;

        $pot = $budgetService->getPotSummary($year, $month);
        $overruns = $budgetService->getOverrunRequests($year, $month, $backdatedOnly);
        $variance = $budgetService->getVarianceByRequest($year, $month);

        $recentRequests = BudgetRequest::query()
            ->where('budget_year', $year)
            ->where('budget_month', $month)
            ->when($backdatedOnly !== null, fn ($q) => $q->where('is_backdated', $backdatedOnly))
            ->with(['spender', 'team', 'category'])
            ->latest()
            ->limit(20)
            ->get();

        return view('hom.dashboard', compact('pot', 'overruns', 'variance', 'recentRequests', 'year', 'month', 'backdatedOnly'));
    }
}
