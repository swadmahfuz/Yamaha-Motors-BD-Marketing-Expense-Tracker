<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[var(--yamaha-black)] uppercase tracking-wide">Dashboard</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card">
                <div class="stat-label">Monthly Budget</div>
                <div class="stat-value">BDT {{ number_format($pot['budget'], 2) }}</div>
            </div>
            <div class="card">
                <div class="stat-label">Committed</div>
                <div class="stat-value">BDT {{ number_format($pot['committed'], 2) }}</div>
            </div>
            <div class="card">
                <div class="stat-label">Spent</div>
                <div class="stat-value">BDT {{ number_format($pot['actual'], 2) }}</div>
            </div>
            <div class="card">
                <div class="stat-label">Available</div>
                <div class="stat-value {{ $pot['available'] < 0 ? 'text-[var(--yamaha-red)]' : '' }}">BDT {{ number_format($pot['available'], 2) }}</div>
            </div>
        </div>

        <div class="card">
            <div class="flex justify-between text-sm mb-2">
                <span>Pot utilization ({{ sprintf('%04d-%02d', $year, $month) }})</span>
                <span>{{ $pot['utilization_pct'] }}%</span>
            </div>
            <div class="pot-bar"><div class="pot-bar-fill" style="width: {{ min(100, $pot['utilization_pct']) }}%"></div></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card">
                <h3 class="font-semibold uppercase tracking-wide text-sm mb-4">Quick actions</h3>
                <div class="flex flex-wrap gap-3">
                    @can('create', App\Models\BudgetRequest::class)
                        <a href="{{ route('requests.create') }}" class="btn-primary">New request</a>
                    @endcan
                    @if(auth()->user()->canAccessApprovals())
                        <a href="{{ route('approvals.index') }}" class="btn-secondary">Approvals ({{ $pendingApprovals }})</a>
                    @endif
                    @role('head_of_marketing|admin|super_admin')
                        <a href="{{ route('hom.dashboard') }}" class="btn-secondary">HoM dashboard</a>
                    @endrole
                </div>
            </div>

            <div class="card">
                <h3 class="font-semibold uppercase tracking-wide text-sm mb-4">Recent requests</h3>
                @forelse($myRequests as $req)
                    <div class="flex justify-between py-2 border-b border-gray-100 text-sm">
                        <a href="{{ route('requests.show', $req) }}" class="hover:text-[var(--yamaha-red)]">{{ $req->reference }}</a>
                        <span class="badge badge-gray">{{ $req->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No requests yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
