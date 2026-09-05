<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Approval chains</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        <div class="card space-y-2">
            <p class="text-sm text-gray-600">
                The approval chain is the spender’s reporting line:
                <strong>spender → manager → … → Head of Marketing</strong> (or any user with no manager as final).
                Edit a user’s <em>Reports to</em> field to change who approves their spend.
            </p>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-xs inline-flex">Manage users</a>
        </div>

        <div class="card space-y-4">
            <h3 class="font-semibold text-sm uppercase tracking-wide">Org hierarchy (who reports to whom)</h3>
            @if ($roots->isEmpty())
                <p class="text-sm text-gray-500">No top-level users (everyone has a manager — check for cycles).</p>
            @else
                <ul class="space-y-3 text-sm">
                    @foreach ($roots as $root)
                        @include('admin.users.partials.tree-node', ['user' => $root, 'depth' => 0, 'allUsers' => $users])
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card overflow-x-auto">
            <h3 class="font-semibold text-sm uppercase tracking-wide mb-4">Predicted approval paths</h3>
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th>Spender</th>
                        <th>Approval path</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chains as $row)
                        <tr>
                            <td class="font-medium">{{ $row['user']->name }}</td>
                            <td class="whitespace-normal">{{ $row['path'] }}</td>
                            <td class="whitespace-normal text-amber-700">{{ $row['warning'] ?? '' }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $row['user']) }}" class="text-xs uppercase tracking-wide text-[var(--yamaha-red)]">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
