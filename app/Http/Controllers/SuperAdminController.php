<?php

namespace App\Http\Controllers;

use App\Enums\BudgetRequestStatus;
use App\Models\BudgetRequest;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function backdates()
    {
        $requests = BudgetRequest::query()
            ->where('status', BudgetRequestStatus::AwaitingSuperAdmin)
            ->with(['spender', 'initiator', 'team'])
            ->latest()
            ->paginate(15);

        return view('super-admin.backdates', compact('requests'));
    }

    public function clear(Request $request, BudgetRequest $budgetRequest, ApprovalService $approvalService): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $approvalService->superAdminClear($budgetRequest, $request->user(), $validated['comment']);

        return back()->with('success', 'Backdated request cleared and sent to approval chain.');
    }

    public function reject(Request $request, BudgetRequest $budgetRequest, ApprovalService $approvalService): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $approvalService->superAdminReject($budgetRequest, $request->user(), $validated['reason']);

        return back()->with('success', 'Backdated request rejected.');
    }
}
