<?php

namespace App\Policies;

use App\Enums\BudgetRequestStatus;
use App\Models\BudgetRequest;
use App\Models\User;

class BudgetRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->canInitiate();
    }

    public function update(User $user, BudgetRequest $budgetRequest): bool
    {
        return $user->id === $budgetRequest->initiator_id
            && $budgetRequest->status === BudgetRequestStatus::Draft;
    }

    public function cancel(User $user, BudgetRequest $budgetRequest): bool
    {
        return in_array($budgetRequest->status, [
            BudgetRequestStatus::Approved,
            BudgetRequestStatus::InProgress,
            BudgetRequestStatus::PartiallyReported,
        ], true) && (
            $user->id === $budgetRequest->initiator_id
            || $user->id === $budgetRequest->spender_id
            || $user->hasAnyRole(['admin', 'head_of_marketing'])
        );
    }

    public function reportActuals(User $user, BudgetRequest $budgetRequest): bool
    {
        if (! in_array($budgetRequest->status, [
            BudgetRequestStatus::Approved,
            BudgetRequestStatus::InProgress,
            BudgetRequestStatus::PartiallyReported,
        ], true)) {
            return false;
        }

        return $user->id === $budgetRequest->initiator_id
            || $user->id === $budgetRequest->spender_id;
    }
}
