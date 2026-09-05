<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Teams</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        <form method="POST" action="{{ route('admin.teams.store') }}" class="card space-y-3">
            @csrf
            <h3 class="font-semibold text-sm uppercase tracking-wide">Add team</h3>
            <div class="grid grid-cols-2 gap-3">
                <input name="name" class="input-field" placeholder="Name" required>
                <input name="code" class="input-field" placeholder="Code" required>
            </div>
            <button class="btn-primary">Create</button>
        </form>

        <div class="card overflow-x-auto">
            <table class="table-minimal">
                <thead><tr><th>Name</th><th>Code</th><th>Active</th><th></th></tr></thead>
                <tbody>
                    @foreach($teams as $team)
                        <tr>
                            <form method="POST" action="{{ route('admin.teams.update', $team) }}">
                                @csrf @method('PATCH')
                                <td><input name="name" value="{{ $team->name }}" class="input-field"></td>
                                <td><input name="code" value="{{ $team->code }}" class="input-field"></td>
                                <td><input type="checkbox" name="is_active" value="1" {{ $team->is_active ? 'checked' : '' }}></td>
                                <td><button class="btn-secondary text-xs">Save</button></td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $teams->links() }}
        </div>
    </div>
</x-app-layout>
