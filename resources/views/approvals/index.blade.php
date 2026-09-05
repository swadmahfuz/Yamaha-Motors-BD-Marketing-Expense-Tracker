<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Pending Approvals</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @forelse($requests as $req)
            <div class="card">
                <div class="flex flex-wrap justify-between gap-4">
                    <div>
                        <h3 class="font-semibold">{{ $req->reference }} — {{ $req->objective }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Spender: {{ $req->spender->name }} · BDT {{ number_format($req->amount_bdt, 2) }} · {{ $req->budgetPeriodLabel() }}
                        </p>
                        <p class="text-sm mt-2">{{ Str::limit($req->description, 200) }}</p>
                    </div>
                    <div class="flex flex-col gap-2 min-w-[200px]">
                        <form method="POST" action="{{ route('approvals.approve', $req) }}">
                            @csrf
                            <textarea name="comment" rows="2" class="input-field mb-2" placeholder="Optional comment"></textarea>
                            <button class="btn-primary w-full">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('approvals.reject', $req) }}">
                            @csrf
                            <textarea name="reason" rows="2" class="input-field mb-2" placeholder="Rejection reason (required)" required></textarea>
                            <button type="submit" class="btn-secondary w-full">Reject</button>
                        </form>
                        <a href="{{ route('requests.show', $req) }}" class="text-center text-sm text-[var(--yamaha-red)]">View details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card text-sm text-gray-500">No pending approvals.</div>
        @endforelse
        {{ $requests->links() }}
    </div>
</x-app-layout>
