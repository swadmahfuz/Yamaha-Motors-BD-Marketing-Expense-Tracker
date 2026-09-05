<?php

namespace App\Services;

use App\Enums\BudgetRequestStatus;
use App\Models\BudgetRequest;
use App\Models\MonthlyBudget;
use Illuminate\Support\Collection;

class BudgetService
{
    public function getMonthlyBudget(int $year, int $month): ?MonthlyBudget
    {
        return MonthlyBudget::where('year', $year)->where('month', $month)->first();
    }

    public function getPotSummary(int $year, int $month): array
    {
        $budget = $this->getMonthlyBudget($year, $month);
        $budgetAmount = (float) ($budget?->amount_bdt ?? 0);

        $requests = BudgetRequest::query()
            ->where('budget_year', $year)
            ->where('budget_month', $month)
            ->withSum('actualExpenses as total_actual', 'amount_bdt')
            ->get();

        $committed = 0;
        $actual = 0;

        foreach ($requests as $request) {
            $requestActual = (float) ($request->total_actual ?? 0);
            $actual += $requestActual;

            if (in_array($request->status->value, BudgetRequestStatus::committedStatuses(), true)) {
                $approved = (float) ($request->approved_amount_bdt ?? $request->amount_bdt);
                $committed += max(0, $approved - $requestActual);
            }
        }

        $available = $budgetAmount - $committed - $actual;

        return [
            'budget' => $budgetAmount,
            'committed' => $committed,
            'actual' => $actual,
            'available' => $available,
            'utilization_pct' => $budgetAmount > 0 ? round((($committed + $actual) / $budgetAmount) * 100, 1) : 0,
        ];
    }

    public function wouldExceedPot(BudgetRequest $request, float $amount): array
    {
        $summary = $this->getPotSummary($request->budget_year, $request->budget_month);
        $projectedAvailable = $summary['available'] - $amount;

        return [
            'exceeds' => $projectedAvailable < 0,
            'projected_available' => $projectedAvailable,
            'summary' => $summary,
        ];
    }

    public function getOverrunRequests(int $year, int $month, ?bool $backdatedOnly = null): Collection
    {
        return BudgetRequest::query()
            ->where('budget_year', $year)
            ->where('budget_month', $month)
            ->when($backdatedOnly !== null, fn ($q) => $q->where('is_backdated', $backdatedOnly))
            ->with(['spender', 'team', 'category', 'actualExpenses'])
            ->get()
            ->filter(fn (BudgetRequest $r) => $r->totalActualAmount() > (float) ($r->approved_amount_bdt ?? $r->amount_bdt));
    }

    public function getVarianceByRequest(int $year, int $month): Collection
    {
        return BudgetRequest::query()
            ->where('budget_year', $year)
            ->where('budget_month', $month)
            ->whereIn('status', [
                BudgetRequestStatus::Closed->value,
                BudgetRequestStatus::InProgress->value,
                BudgetRequestStatus::PartiallyReported->value,
                BudgetRequestStatus::Approved->value,
            ])
            ->with(['team', 'category', 'spender'])
            ->get()
            ->map(function (BudgetRequest $request) {
                $approved = (float) ($request->approved_amount_bdt ?? $request->amount_bdt);
                $actual = $request->totalActualAmount();

                return [
                    'request' => $request,
                    'approved' => $approved,
                    'actual' => $actual,
                    'variance' => $actual - $approved,
                    'variance_pct' => $approved > 0 ? round((($actual - $approved) / $approved) * 100, 1) : 0,
                ];
            });
    }
}
