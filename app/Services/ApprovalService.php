<?php

namespace App\Services;

use App\Enums\BudgetRequestStatus;
use App\Models\ApprovalStep;
use App\Models\BudgetRequest;
use App\Models\User;
use App\Notifications\ApprovalNeededNotification;
use App\Notifications\BackdateQueueNotification;
use App\Notifications\RequestOutcomeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ApprovalService
{
    public function __construct(
        private AuditService $audit,
        private BudgetService $budget,
    ) {}

    public function submit(BudgetRequest $request): BudgetRequest
    {
        return DB::transaction(function () use ($request) {
            $isBackdated = $request->request_date->lt(today());

            $request->update([
                'is_backdated' => $isBackdated,
                'submitted_at' => now(),
                'status' => $isBackdated
                    ? BudgetRequestStatus::AwaitingSuperAdmin
                    : BudgetRequestStatus::InApproval,
            ]);

            if ($isBackdated) {
                $this->notifySuperAdmins($request);
                $this->audit->log('budget_request.submitted_backdated', $request, null, [
                    'status' => $request->status->value,
                ], 'Backdated request submitted for Super Admin clearance');
            } else {
                $this->buildApprovalChain($request);
                $this->notifyCurrentApprover($request);
                $this->audit->log('budget_request.submitted', $request, null, [
                    'status' => $request->status->value,
                ]);
            }

            return $request->fresh();
        });
    }

    public function superAdminClear(BudgetRequest $request, User $admin, string $comment): BudgetRequest
    {
        return DB::transaction(function () use ($request, $admin, $comment) {
            $request->update([
                'super_admin_cleared_by' => $admin->id,
                'super_admin_cleared_at' => now(),
                'super_admin_comment' => $comment,
                'status' => BudgetRequestStatus::InApproval,
            ]);

            $this->buildApprovalChain($request);
            $this->notifyCurrentApprover($request);

            $this->audit->log('budget_request.backdate_cleared', $request, null, [
                'super_admin_comment' => $comment,
            ], 'Super Admin cleared backdated request');

            return $request->fresh();
        });
    }

    public function superAdminReject(BudgetRequest $request, User $admin, string $reason): BudgetRequest
    {
        return DB::transaction(function () use ($request, $admin, $reason) {
            $request->update([
                'status' => BudgetRequestStatus::Rejected,
                'super_admin_cleared_by' => $admin->id,
                'super_admin_cleared_at' => now(),
                'super_admin_comment' => $reason,
            ]);

            $request->initiator->notify(new RequestOutcomeNotification($request, 'rejected', $reason));
            $this->audit->log('budget_request.backdate_rejected', $request, null, [
                'reason' => $reason,
            ]);

            return $request->fresh();
        });
    }

    public function approveStep(BudgetRequest $request, User $approver, ?string $comment = null): array
    {
        return DB::transaction(function () use ($request, $approver, $comment) {
            $step = $request->approvalSteps()
                ->where('step_order', $request->current_approval_step)
                ->where('approver_id', $approver->id)
                ->where('status', 'pending')
                ->firstOrFail();

            $potWarning = $this->budget->wouldExceedPot($request, (float) $request->amount_bdt);

            $step->update([
                'status' => 'approved',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $isFinal = $this->isFinalStep($request, $step);

            if ($isFinal) {
                $request->update([
                    'status' => BudgetRequestStatus::Approved,
                    'approved_amount_bdt' => $request->amount_bdt,
                    'approved_at' => now(),
                    'current_approval_step' => null,
                ]);

                $request->initiator->notify(new RequestOutcomeNotification($request, 'approved'));
                $request->spender->notify(new RequestOutcomeNotification($request, 'approved'));

                $this->audit->log('budget_request.approved', $request, null, [
                    'approved_amount_bdt' => $request->amount_bdt,
                    'pot_warning' => $potWarning['exceeds'],
                ]);
            } else {
                $nextStep = $request->current_approval_step + 1;
                $request->update(['current_approval_step' => $nextStep]);
                $this->notifyCurrentApprover($request);

                $this->audit->log('budget_request.step_approved', $request, null, [
                    'step' => $step->step_order,
                    'comment' => $comment,
                ]);
            }

            return [
                'request' => $request->fresh(),
                'pot_warning' => $potWarning,
                'is_final' => $isFinal,
            ];
        });
    }

    public function rejectStep(BudgetRequest $request, User $approver, string $reason): BudgetRequest
    {
        return DB::transaction(function () use ($request, $approver, $reason) {
            $step = $request->approvalSteps()
                ->where('step_order', $request->current_approval_step)
                ->where('approver_id', $approver->id)
                ->where('status', 'pending')
                ->firstOrFail();

            $step->update([
                'status' => 'rejected',
                'comment' => $reason,
                'acted_at' => now(),
            ]);

            $request->update([
                'status' => BudgetRequestStatus::Rejected,
                'current_approval_step' => null,
            ]);

            $request->initiator->notify(new RequestOutcomeNotification($request, 'rejected', $reason));
            $request->spender->notify(new RequestOutcomeNotification($request, 'rejected', $reason));

            $this->audit->log('budget_request.rejected', $request, null, ['reason' => $reason]);

            return $request->fresh();
        });
    }

    public function cancel(BudgetRequest $request, User $user): BudgetRequest
    {
        return DB::transaction(function () use ($request, $user) {
            $oldStatus = $request->status->value;

            $request->update([
                'status' => BudgetRequestStatus::Cancelled,
                'current_approval_step' => null,
            ]);

            $this->audit->log('budget_request.cancelled', $request, ['status' => $oldStatus], [
                'status' => BudgetRequestStatus::Cancelled->value,
            ], "Cancelled by {$user->name}");

            return $request->fresh();
        });
    }

    public function buildApprovalChain(BudgetRequest $request): void
    {
        $request->approvalSteps()->delete();

        $spender = $request->spender;
        $approvers = [];
        $current = $spender->manager;

        while ($current) {
            $approvers[] = $current;

            if ($current->isFinalApprover()) {
                break;
            }

            $current = $current->manager;

            if (count($approvers) > 20) {
                break;
            }
        }

        if (empty($approvers)) {
            $hom = User::role('head_of_marketing')->first();
            if ($hom) {
                $approvers[] = $hom;
            }
        }

        $stepOrder = 1;
        foreach ($approvers as $approver) {
            ApprovalStep::create([
                'budget_request_id' => $request->id,
                'step_order' => $stepOrder,
                'approver_id' => $approver->id,
                'status' => 'pending',
            ]);
            $stepOrder++;
        }

        $request->update(['current_approval_step' => $approvers ? 1 : null]);
    }

    private function isFinalStep(BudgetRequest $request, ApprovalStep $step): bool
    {
        $approver = $step->approver;

        if ($approver->isFinalApprover()) {
            return true;
        }

        return ! $request->approvalSteps()
            ->where('step_order', '>', $step->step_order)
            ->exists();
    }

    private function notifyCurrentApprover(BudgetRequest $request): void
    {
        if (! $request->current_approval_step) {
            return;
        }

        $step = $request->approvalSteps()
            ->where('step_order', $request->current_approval_step)
            ->with('approver')
            ->first();

        if ($step?->approver) {
            $step->approver->notify(new ApprovalNeededNotification($request));
        }
    }

    private function notifySuperAdmins(BudgetRequest $request): void
    {
        $superAdmins = User::role('super_admin')->get();
        Notification::send($superAdmins, new BackdateQueueNotification($request));
    }
}
