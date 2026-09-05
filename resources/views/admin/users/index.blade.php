<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl uppercase tracking-wide">Users</h2>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">Add user</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        @if (session('success'))
            <div class="card text-sm text-green-800 bg-green-50 border-green-200">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="card grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="stat-label">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] }}" class="input-field" placeholder="Name or email">
            </div>
            <div>
                <label class="stat-label">Team</label>
                <select name="team_id" class="input-field">
                    <option value="">All teams</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected((string) $filters['team_id'] === (string) $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="stat-label">Role</label>
                <select name="role" class="input-field">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected($filters['role'] === $role->name)>{{ str_replace('_', ' ', $role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="inactive_only" value="1" @checked($filters['inactive_only'])>
                    Inactive only
                </label>
                <button class="btn-secondary text-xs">Filter</button>
            </div>
        </form>

        <div class="card overflow-x-auto">
            <p class="text-sm text-gray-600 mb-4">
                Approval follows each spender’s <strong>Reports to</strong> line (manager → … → Head of Marketing).
                Edit a user to change their manager.
            </p>
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Team</th>
                        <th>Reports to</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td>{{ $user->team?->name ?? '—' }}</td>
                            <td>{{ $user->manager?->name ?? '— (top of chain)' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-gray">{{ str_replace('_', ' ', $role->name) }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge badge-green">Active</span>
                                @else
                                    <span class="badge badge-red">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-gray-500">No users match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
