<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = BudgetRequest::query()->with(['spender', 'team', 'category', 'initiator']);

        $user = $request->user();

        if (! $user->hasAnyRole(['admin', 'super_admin', 'head_of_marketing'])) {
            $query->where(function ($q) use ($user) {
                $q->where('initiator_id', $user->id)
                    ->orWhere('spender_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('backdated')) {
            $query->where('is_backdated', $request->boolean('backdated'));
        }

        $requests = $query->latest()->paginate(15);

        return view('requests.index', compact('requests'));
    }

    public function create(): View
    {
        $this->authorize('create', BudgetRequest::class);

        return view('requests.create');
    }

    public function show(BudgetRequest $request): View
    {
        $request->load(['spender', 'team', 'category', 'initiator', 'approvalSteps.approver', 'actualExpenses.reporter', 'attachments', 'actualExpenses.attachments']);

        return view('requests.show', ['budgetRequest' => $request]);
    }

    public function edit(BudgetRequest $request): View
    {
        $this->authorize('update', $request);

        return view('requests.edit', ['budgetRequest' => $request]);
    }
}
