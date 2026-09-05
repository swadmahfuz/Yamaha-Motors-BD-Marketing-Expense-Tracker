<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** @var list<string> */
    private array $assignableRoles = [
        'staff',
        'initiator',
        'spender',
        'approver',
        'head_of_marketing',
        'admin',
        'super_admin',
    ];

    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['team', 'manager', 'roles'])
            ->orderBy('name');

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->integer('team_id'));
        }

        if ($request->filled('role')) {
            $query->role($request->string('role')->toString());
        }

        if ($request->boolean('inactive_only')) {
            $query->where('is_active', false);
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'teams' => Team::orderBy('name')->get(),
            'roles' => $this->rolesForActor(),
            'filters' => [
                'q' => $search ?? '',
                'team_id' => $request->get('team_id'),
                'role' => $request->get('role'),
                'inactive_only' => $request->boolean('inactive_only'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $this->validated($request);
        $roles = $this->validatedRoles($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'team_id' => $data['team_id'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        $user->syncRoles($roles);

        $audit->log('user.created', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'team_id' => $user->team_id,
            'manager_id' => $user->manager_id,
            'roles' => $roles,
            'is_active' => $user->is_active,
        ]);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'User created. Approval chain follows their reporting line.');
    }

    public function edit(User $user): View
    {
        $user->load(['team', 'manager', 'roles', 'directReports']);

        return view('admin.users.edit', array_merge($this->formData($user), [
            'user' => $user,
            'approvalChain' => $user->approvalChainUsers(),
            'chainWarning' => $this->chainWarning($user),
        ]));
    }

    public function update(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $roles = $this->validatedRoles($request, $user);

        $managerId = ! empty($data['manager_id']) ? (int) $data['manager_id'] : null;

        if ($this->wouldCreateManagerCycle($user, $managerId)) {
            throw ValidationException::withMessages([
                'manager_id' => 'That manager would create a reporting-line cycle.',
            ]);
        }

        $old = [
            'name' => $user->name,
            'email' => $user->email,
            'team_id' => $user->team_id,
            'manager_id' => $user->manager_id,
            'roles' => $user->getRoleNames()->values()->all(),
            'is_active' => $user->is_active,
        ];

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'team_id' => $data['team_id'] ?? null,
            'manager_id' => $managerId,
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->syncRoles($roles);

        $audit->log('user.updated', $user, $old, [
            'name' => $user->name,
            'email' => $user->email,
            'team_id' => $user->team_id,
            'manager_id' => $user->manager_id,
            'roles' => $roles,
            'is_active' => $user->is_active,
            'password_changed' => ! empty($data['password']),
        ]);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'User updated. New requests will use the updated approval chain.');
    }

    public function chains(): View
    {
        $users = User::query()
            ->with(['manager', 'roles', 'directReports' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        $roots = $users->filter(fn (User $user) => $user->manager_id === null);

        $chains = $users
            ->filter(fn (User $user) => $user->canAppearAsSpender() || $user->is_active)
            ->map(function (User $user) {
                $chain = $user->approvalChainUsers();

                return [
                    'user' => $user,
                    'chain' => $chain,
                    'path' => collect([$user->name])
                        ->merge($chain->pluck('name'))
                        ->implode(' → '),
                    'warning' => $this->chainWarning($user),
                ];
            })
            ->values();

        return view('admin.users.chains', [
            'roots' => $roots,
            'users' => $users,
            'chains' => $chains,
        ]);
    }

    private function formData(?User $except = null): array
    {
        $managers = User::query()
            ->where('is_active', true)
            ->when($except, fn ($q) => $q->where('id', '!=', $except->id))
            ->orderBy('name')
            ->get();

        return [
            'teams' => Team::where('is_active', true)->orderBy('name')->get(),
            'managers' => $managers,
            'roles' => $this->rolesForActor(),
        ];
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
            'team_id' => ['nullable', 'exists:teams,id'],
            'manager_id' => [
                'nullable',
                'exists:users,id',
                Rule::notIn(array_filter([$user?->id])),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /**
     * @return list<string>
     */
    private function validatedRoles(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($this->assignableRoles)],
        ]);

        $roles = array_values(array_unique($data['roles']));
        $actor = auth()->user();

        if (in_array('super_admin', $roles, true) && ! $actor->hasRole('super_admin')) {
            throw ValidationException::withMessages([
                'roles' => 'Only a Super Admin can assign the Super Admin role.',
            ]);
        }

        if (
            $user
            && $user->hasRole('super_admin')
            && ! in_array('super_admin', $roles, true)
            && ! $actor->hasRole('super_admin')
        ) {
            throw ValidationException::withMessages([
                'roles' => 'Only a Super Admin can remove the Super Admin role.',
            ]);
        }

        if (
            $user
            && $user->id === $actor->id
            && $user->hasAnyRole(['admin', 'super_admin'])
            && ! in_array('admin', $roles, true)
            && ! in_array('super_admin', $roles, true)
        ) {
            throw ValidationException::withMessages([
                'roles' => 'You cannot remove your own admin access.',
            ]);
        }

        return $roles;
    }

    private function rolesForActor()
    {
        $roles = Role::query()
            ->whereIn('name', $this->assignableRoles)
            ->orderBy('name')
            ->get();

        if (! auth()->user()->hasRole('super_admin')) {
            $roles = $roles->reject(fn (Role $role) => $role->name === 'super_admin')->values();
        }

        return $roles;
    }

    private function wouldCreateManagerCycle(User $user, ?int $managerId): bool
    {
        if (! $managerId) {
            return false;
        }

        if ($managerId === $user->id) {
            return true;
        }

        $current = User::find($managerId);
        $seen = [$user->id => true];

        while ($current) {
            if (isset($seen[$current->id])) {
                return true;
            }

            $seen[$current->id] = true;
            $current = $current->manager;
        }

        return false;
    }

    private function chainWarning(User $user): ?string
    {
        if (! $user->is_active) {
            return 'Inactive — will not appear as spender.';
        }

        $chain = $user->approvalChainUsers();

        if ($chain->isEmpty()) {
            return 'No manager and no Head of Marketing — requests cannot enter approval.';
        }

        $final = $chain->last();

        if ($final && ! $final->hasRole('head_of_marketing') && $final->manager_id === null) {
            return 'Chain ends at '.$final->name.' (no manager). They act as final approver without HoM role.';
        }

        if ($chain->contains(fn (User $approver) => ! $approver->is_active)) {
            return 'Chain includes an inactive manager.';
        }

        return null;
    }
}
