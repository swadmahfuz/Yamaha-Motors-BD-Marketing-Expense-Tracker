<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl uppercase tracking-wide">{{ $budgetRequest->reference }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $budgetRequest->objective }}</p>
            </div>
            <div class="flex gap-2">
                @can('reportActuals', $budgetRequest)
                    <a href="{{ route('actuals.create', $budgetRequest) }}" class="btn-primary">Report actuals</a>
                @endcan
                @can('cancel', $budgetRequest)
                    <form method="POST" action="{{ route('requests.cancel', $budgetRequest) }}" onsubmit="return confirm('Cancel this request and release commitment?')">
                        @csrf
                        <button class="btn-secondary">Cancel</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card lg:col-span-2 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Status</span><div class="font-medium">{{ $budgetRequest->status->label() }}</div></div>
                    <div><span class="text-gray-500">Amount</span><div class="font-medium">BDT {{ number_format($budgetRequest->amount_bdt, 2) }}</div></div>
                    <div><span class="text-gray-500">Spender</span><div>{{ $budgetRequest->spender->name }}</div></div>
                    <div><span class="text-gray-500">Initiator</span><div>{{ $budgetRequest->initiator->name }}</div></div>
                    <div><span class="text-gray-500">Team</span><div>{{ $budgetRequest->team->name }}</div></div>
                    <div><span class="text-gray-500">Category</span><div>{{ $budgetRequest->category->name }}</div></div>
                    <div><span class="text-gray-500">Request date</span><div>{{ $budgetRequest->request_date->format('Y-m-d') }}</div></div>
                    <div><span class="text-gray-500">Budget month</span><div>{{ $budgetRequest->budgetPeriodLabel() }}</div></div>
                    <div><span class="text-gray-500">Activity</span><div>{{ $budgetRequest->activity_start_date->format('Y-m-d') }} – {{ $budgetRequest->activity_end_date->format('Y-m-d') }}</div></div>
                    <div><span class="text-gray-500">Location</span><div>{{ $budgetRequest->location }}</div></div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide mb-1">Description</h4>
                    <p class="text-sm">{{ $budgetRequest->description }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide mb-1">Expected outcome</h4>
                    <p class="text-sm">{{ $budgetRequest->expected_outcome }}</p>
                </div>
                @if($budgetRequest->is_backdated)
                    <div class="border-l-4 border-[var(--yamaha-red)] pl-4">
                        <h4 class="text-sm font-semibold text-[var(--yamaha-red)]">Backdate</h4>
                        <p class="text-sm">{{ $budgetRequest->backdate_reason }}</p>
                        @if($budgetRequest->backdate_evidence)<p class="text-sm mt-1 text-gray-600">{{ $budgetRequest->backdate_evidence }}</p>@endif
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h3 class="font-semibold uppercase tracking-wide text-sm mb-3">Approval chain</h3>
                    @forelse($budgetRequest->approvalSteps as $step)
                        <div class="flex justify-between py-2 border-b text-sm">
                            <span>{{ $step->approver->name }}</span>
                            <span class="badge {{ $step->status === 'approved' ? 'badge-green' : ($step->status === 'rejected' ? 'badge-red' : 'badge-gray') }}">{{ ucfirst($step->status) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No approval steps yet.</p>
                    @endforelse
                </div>

                <div class="card">
                    <h3 class="font-semibold uppercase tracking-wide text-sm mb-3">Actuals</h3>
                    @php $totalActual = $budgetRequest->totalActualAmount(); @endphp
                    <p class="text-sm mb-2">Total: BDT {{ number_format($totalActual, 2) }}
                        @if($budgetRequest->approved_amount_bdt && $totalActual > $budgetRequest->approved_amount_bdt)
                            <span class="badge badge-red">Overrun</span>
                        @endif
                    </p>
                    @foreach($budgetRequest->actualExpenses as $expense)
                        <div class="py-2 border-b text-sm">
                            <div class="font-medium">BDT {{ number_format($expense->amount_bdt, 2) }} — {{ $expense->spend_date->format('Y-m-d') }}</div>
                            <div class="text-gray-500">{{ $expense->vendor }} · {{ $expense->reporter->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
