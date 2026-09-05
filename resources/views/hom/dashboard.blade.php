<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl uppercase tracking-wide">Head of Marketing Dashboard</h2>
            <a href="{{ route('export.requests', ['year' => $year, 'month' => $month]) }}" class="btn-secondary">Export CSV</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <form method="GET" class="card flex flex-wrap gap-4 items-end">
            <div>
                <label class="stat-label block mb-1">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="input-field w-28">
            </div>
            <div>
                <label class="stat-label block mb-1">Month</label>
                <input type="number" name="month" min="1" max="12" value="{{ $month }}" class="input-field w-24">
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="backdated" value="1" {{ $backdatedOnly ? 'checked' : '' }}>
                    Backdated only
                </label>
            </div>
            <button class="btn-primary">Filter</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card"><div class="stat-label">Budget</div><div class="stat-value">BDT {{ number_format($pot['budget'], 2) }}</div></div>
            <div class="card"><div class="stat-label">Committed</div><div class="stat-value">BDT {{ number_format($pot['committed'], 2) }}</div></div>
            <div class="card"><div class="stat-label">Spent</div><div class="stat-value">BDT {{ number_format($pot['actual'], 2) }}</div></div>
            <div class="card"><div class="stat-label">Available</div><div class="stat-value {{ $pot['available'] < 0 ? 'text-[var(--yamaha-red)]' : '' }}">BDT {{ number_format($pot['available'], 2) }}</div></div>
        </div>

        <div class="card">
            <div class="flex justify-between text-sm mb-2"><span>Utilization</span><span>{{ $pot['utilization_pct'] }}%</span></div>
            <div class="pot-bar"><div class="pot-bar-fill" style="width: {{ min(100, $pot['utilization_pct']) }}%"></div></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card">
                <h3 class="font-semibold uppercase tracking-wide text-sm mb-4">Overruns</h3>
                @forelse($overruns as $req)
                    <div class="py-2 border-b text-sm flex justify-between">
                        <a href="{{ route('requests.show', $req) }}" class="hover:text-[var(--yamaha-red)]">{{ $req->reference }}</a>
                        <span class="badge badge-red">+BDT {{ number_format($req->varianceAmount(), 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No overruns this period.</p>
                @endforelse
            </div>

            <div class="card overflow-x-auto">
                <h3 class="font-semibold uppercase tracking-wide text-sm mb-4">Variance</h3>
                <table class="table-minimal">
                    <thead><tr><th>Request</th><th>Approved</th><th>Actual</th><th>Var %</th></tr></thead>
                    <tbody>
                        @foreach($variance->take(10) as $row)
                            <tr>
                                <td>{{ $row['request']->reference }}</td>
                                <td>{{ number_format($row['approved'], 0) }}</td>
                                <td>{{ number_format($row['actual'], 0) }}</td>
                                <td class="{{ $row['variance'] > 0 ? 'text-[var(--yamaha-red)]' : '' }}">{{ $row['variance_pct'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
