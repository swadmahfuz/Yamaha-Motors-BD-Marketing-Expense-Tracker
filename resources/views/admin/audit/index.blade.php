<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Audit Log</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        <div class="card overflow-x-auto">
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td class="text-xs">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td><code class="text-xs">{{ $log->action }}</code></td>
                            <td class="text-sm">{{ $log->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
