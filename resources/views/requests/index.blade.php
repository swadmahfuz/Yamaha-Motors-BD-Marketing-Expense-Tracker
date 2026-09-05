<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl uppercase tracking-wide">Budget Requests</h2>
            @can('create', App\Models\BudgetRequest::class)
                <a href="{{ route('requests.create') }}" class="btn-primary">New request</a>
            @endcan
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="card overflow-x-auto">
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Spender</th>
                        <th>Amount</th>
                        <th>Budget month</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($requests as $req)
                        <tr>
                            <td>
                                {{ $req->reference }}
                                @if($req->is_backdated)<span class="badge badge-red ml-1">Backdated</span>@endif
                            </td>
                            <td>{{ $req->spender->name }}</td>
                            <td>BDT {{ number_format($req->amount_bdt, 2) }}</td>
                            <td>{{ $req->budgetPeriodLabel() }}</td>
                            <td><span class="badge badge-gray">{{ $req->status->label() }}</span></td>
                            <td><a href="{{ route('requests.show', $req) }}" class="text-[var(--yamaha-red)] text-sm">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $requests->links() }}</div>
        </div>
    </div>
</x-app-layout>
