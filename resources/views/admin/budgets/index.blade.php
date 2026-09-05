<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Monthly Budgets</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        <form method="POST" action="{{ route('admin.budgets.store') }}" class="card space-y-3">
            @csrf
            <h3 class="font-semibold text-sm uppercase tracking-wide">Set monthly pot</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="number" name="year" value="{{ now()->year }}" class="input-field" required>
                <input type="number" name="month" min="1" max="12" value="{{ now()->month }}" class="input-field" required>
                <input type="number" step="0.01" name="amount_bdt" class="input-field md:col-span-2" placeholder="Amount BDT" required>
            </div>
            <textarea name="notes" rows="2" class="input-field" placeholder="Notes (optional)"></textarea>
            <button class="btn-primary">Save budget</button>
        </form>

        <div class="card overflow-x-auto">
            <table class="table-minimal">
                <thead><tr><th>Period</th><th>Amount BDT</th><th>Set by</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach($budgets as $budget)
                        <tr>
                            <td>{{ $budget->periodLabel() }}</td>
                            <td>{{ number_format($budget->amount_bdt, 2) }}</td>
                            <td>{{ $budget->setter?->name }}</td>
                            <td>{{ $budget->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $budgets->links() }}
        </div>
    </div>
</x-app-layout>
