<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canAccessApprovals(), 403);

        $requests = BudgetRequest::query()
            ->where('status', 'in_approval')
            ->whereHas('approvalSteps', function ($q) use ($request) {
                $q->where('approver_id', $request->user()->id)
                    ->where('status', 'pending')
                    ->whereColumn('approval_steps.step_order', 'budget_requests.current_approval_step');
            })
            ->with(['spender', 'team', 'category'])
            ->latest()
            ->paginate(15);

        return view('approvals.index', compact('requests'));
    }

    public function approve(Request $request, BudgetRequest $budgetRequest, ApprovalService $approvalService): RedirectResponse
    {
        $this->authorizeCurrentApprover($budgetRequest, $request->user());

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $approvalService->approveStep($budgetRequest, $request->user(), $validated['comment'] ?? null);

        $message = $result['is_final']
            ? 'Request approved and committed to the monthly pot.'
            : 'Approval recorded and forwarded to the next approver.';

        if ($result['pot_warning']['exceeds']) {
            $message .= ' Warning: this approval exceeds the monthly pot (soft limit).';
        }

        return redirect()->route('approvals.index')->with('success', $message);
    }

    public function reject(Request $request, BudgetRequest $budgetRequest, ApprovalService $approvalService): RedirectResponse
    {
        $this->authorizeCurrentApprover($budgetRequest, $request->user());

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $approvalService->rejectStep($budgetRequest, $request->user(), $validated['reason']);

        return redirect()->route('approvals.index')->with('success', 'Request rejected.');
    }

    private function authorizeCurrentApprover(BudgetRequest $budgetRequest, User $user): void
    {
        $isCurrentApprover = $budgetRequest->approvalSteps()
            ->where('step_order', $budgetRequest->current_approval_step)
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        abort_unless($isCurrentApprover, 403);
    }
}
