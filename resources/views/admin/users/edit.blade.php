<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Edit user</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        @if (session('success'))
            <div class="card text-sm text-green-800 bg-green-50 border-green-200">{{ session('success') }}</div>
        @endif

        <div class="card space-y-3">
            <h3 class="font-semibold text-sm uppercase tracking-wide">Approval chain preview</h3>
            <p class="text-sm text-gray-600">
                When <strong>{{ $user->name }}</strong> is the spender, new requests escalate:
            </p>
            @if ($approvalChain->isEmpty())
                <p class="text-sm text-[var(--yamaha-red)]">No approvers — set a manager or ensure a Head of Marketing exists.</p>
            @else
                <ol class="text-sm space-y-1 list-decimal list-inside">
                    <li class="text-gray-500">{{ $user->name }} <span class="text-xs">(spender)</span></li>
                    @foreach ($approvalChain as $index => $approver)
                        <li>
                            {{ $approver->name }}
                            @if ($loop->last)
                                <span class="badge badge-gray ml-1">final</span>
                            @else
                                <span class="text-xs text-gray-500">step {{ $index + 1 }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
            @if ($chainWarning)
                <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2">{{ $chainWarning }}</p>
            @endif
            @if ($user->directReports->isNotEmpty())
                <p class="text-xs text-gray-500">
                    Direct reports: {{ $user->directReports->pluck('name')->join(', ') }}
                </p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="stat-label">Name</label>
                    <input name="name" value="{{ old('name', $user->name) }}" class="input-field" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label class="stat-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-field" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label class="stat-label">New password</label>
                    <input type="password" name="password" class="input-field" placeholder="Leave blank to keep">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <label class="stat-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="input-field">
                </div>
                <div>
                    <label class="stat-label">Team</label>
                    <select name="team_id" class="input-field">
                        <option value="">No team</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $user->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="stat-label">Reports to (manager)</label>
                    <select name="manager_id" class="input-field">
                        <option value="">None — top of approval chain</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_id', $user->manager_id) == $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Changing this updates the approval chain for future requests only.</p>
                    <x-input-error :messages="$errors->get('manager_id')" class="mt-1" />
                </div>
            </div>

            <div>
                <label class="stat-label mb-2 block">Roles</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach ($roles as $role)
                        <label class="inline-flex items-center gap-2 text-sm border border-[var(--yamaha-silver)] px-3 py-2">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                @checked(collect(old('roles', $user->getRoleNames()->all()))->contains($role->name))>
                            {{ str_replace('_', ' ', $role->name) }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('roles')" class="mt-1" />
            </div>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                Active
            </label>

            <div class="flex gap-3">
                <button class="btn-primary">Save changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back to list</a>
            </div>
        </form>
    </div>
</x-app-layout>
