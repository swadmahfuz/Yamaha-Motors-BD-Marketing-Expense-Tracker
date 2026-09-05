<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Add user</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        <form method="POST" action="{{ route('admin.users.store') }}" class="card space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="stat-label">Name</label>
                    <input name="name" value="{{ old('name') }}" class="input-field" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label class="stat-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input-field" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label class="stat-label">Password</label>
                    <input type="password" name="password" class="input-field" required>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <label class="stat-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="input-field" required>
                </div>
                <div>
                    <label class="stat-label">Team</label>
                    <select name="team_id" class="input-field">
                        <option value="">No team</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="stat-label">Reports to (manager)</label>
                    <select name="manager_id" class="input-field">
                        <option value="">None — top of approval chain</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_id') == $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">This sets who must approve their spend requests.</p>
                    <x-input-error :messages="$errors->get('manager_id')" class="mt-1" />
                </div>
            </div>

            <div>
                <label class="stat-label mb-2 block">Roles</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach ($roles as $role)
                        <label class="inline-flex items-center gap-2 text-sm border border-[var(--yamaha-silver)] px-3 py-2">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                @checked(collect(old('roles', ['staff']))->contains($role->name))>
                            {{ str_replace('_', ' ', $role->name) }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('roles')" class="mt-1" />
            </div>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Active
            </label>

            <div class="flex gap-3">
                <button class="btn-primary">Create user</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
