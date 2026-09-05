<?php

namespace App\Http\Controllers;

use App\Enums\BudgetRequestStatus;
use App\Models\BudgetRequest;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BudgetService $budgetService): View
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $pot = $budgetService->getPotSummary($year, $month);

        $myRequests = BudgetRequest::query()
            ->when(
                ! $user->hasAnyRole(['admin', 'super_admin', 'head_of_marketing']),
                fn ($q) => $q->where(function ($q) use ($user) {
                    $q->where('initiator_id', $user->id)
                        ->orWhere('spender_id', $user->id);
                })
            )
            ->latest()
            ->limit(5)
            ->get();

        $pendingApprovals = BudgetRequest::query()
            ->where('status', BudgetRequestStatus::InApproval)
            ->whereHas('approvalSteps', function ($q) use ($user) {
                $q->where('approver_id', $user->id)
                    ->where('status', 'pending')
                    ->whereColumn('approval_steps.step_order', 'budget_requests.current_approval_step');
            })
            ->count();

        return view('dashboard', compact('pot', 'myRequests', 'pendingApprovals', 'year', 'month'));
    }
}
