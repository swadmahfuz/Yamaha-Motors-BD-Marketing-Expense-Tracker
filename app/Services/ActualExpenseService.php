<?php

namespace App\Services;

use App\Enums\BudgetRequestStatus;
use App\Models\ActualExpense;
use App\Models\BudgetRequest;
use Illuminate\Support\Facades\DB;

class ActualExpenseService
{
    public function __construct(
        private AuditService $audit,
        private AttachmentService $attachments,
    ) {}

    public function record(
        BudgetRequest $request,
        array $data,
        array $files = [],
        bool $closeRequest = false,
    ): ActualExpense {
        return DB::transaction(function () use ($request, $data, $files, $closeRequest) {
            $expense = $request->actualExpenses()->create([
                'reported_by' => auth()->id(),
                'amount_bdt' => $data['amount_bdt'],
                'spend_date' => $data['spend_date'],
                'vendor' => $data['vendor'],
                'description' => $data['description'],
                'overrun_justification' => $data['overrun_justification'] ?? null,
            ]);

            if (! empty($files)) {
                $this->attachments->storeMany($expense, $files, 'actual-receipts');
            }

            $newTotal = $request->fresh()->totalActualAmount();
            $approved = (float) ($request->approved_amount_bdt ?? $request->amount_bdt);

            if ($closeRequest) {
                $request->update([
                    'status' => BudgetRequestStatus::Closed,
                    'closed_at' => now(),
                ]);
            } elseif ($newTotal > 0 && $newTotal < $approved) {
                $request->update(['status' => BudgetRequestStatus::PartiallyReported]);
            } elseif ($newTotal >= $approved) {
                $request->update(['status' => BudgetRequestStatus::InProgress]);
            } else {
                $request->update(['status' => BudgetRequestStatus::InProgress]);
            }

            $this->audit->log('actual_expense.recorded', $expense, null, [
                'amount_bdt' => $expense->amount_bdt,
                'budget_request_id' => $request->id,
                'closed' => $closeRequest,
            ]);

            return $expense;
        });
    }
}
