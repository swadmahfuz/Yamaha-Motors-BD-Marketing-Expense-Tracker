<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Notifications</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="card divide-y">
            @forelse($notifications as $notification)
                <div class="py-4 {{ $notification->read_at ? 'opacity-60' : '' }}">
                    <p class="text-sm">{{ $notification->data['message'] ?? 'Notification' }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    @if(!$notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-2">
                            @csrf
                            <button class="text-xs text-[var(--yamaha-red)]">Mark read</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 py-4">No notifications.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
