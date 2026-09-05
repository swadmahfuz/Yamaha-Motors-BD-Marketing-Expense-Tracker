<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Backdated Request Queue</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @forelse($requests as $req)
            <div class="card">
                <div class="flex flex-wrap justify-between gap-4">
                    <div>
                        <h3 class="font-semibold">{{ $req->reference }}</h3>
                        <p class="text-sm text-gray-600">Request date: {{ $req->request_date->format('Y-m-d') }} · Spender: {{ $req->spender->name }}</p>
                        <p class="text-sm mt-2">{{ $req->backdate_reason }}</p>
                    </div>
                    <div class="min-w-[240px] space-y-2">
                        <form method="POST" action="{{ route('super-admin.clear', $req) }}">
                            @csrf
                            <textarea name="comment" class="input-field mb-2" rows="2" placeholder="Clearance comment" required></textarea>
                            <button class="btn-primary w-full">Clear & send to chain</button>
                        </form>
                        <form method="POST" action="{{ route('super-admin.reject', $req) }}">
                            @csrf
                            <textarea name="reason" class="input-field mb-2" rows="2" placeholder="Rejection reason" required></textarea>
                            <button class="btn-secondary w-full">Reject</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card text-sm text-gray-500">No backdated requests awaiting clearance.</div>
        @endforelse
        {{ $requests->links() }}
    </div>
</x-app-layout>
