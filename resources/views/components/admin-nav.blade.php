@php
    $tabs = [
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'except' => 'admin.users.chains', 'label' => 'Users'],
        ['route' => 'admin.users.chains', 'match' => 'admin.users.chains', 'label' => 'Approval chains'],
        ['route' => 'admin.teams.index', 'match' => 'admin.teams.*', 'label' => 'Teams'],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Categories'],
        ['route' => 'admin.budgets.index', 'match' => 'admin.budgets.*', 'label' => 'Monthly budgets'],
        ['route' => 'admin.audit.index', 'match' => 'admin.audit.*', 'label' => 'Audit'],
    ];
@endphp

<div class="flex flex-wrap gap-4 text-sm">
    @foreach ($tabs as $tab)
        @php
            $active = isset($tab['except'])
                ? request()->routeIs($tab['match']) && ! request()->routeIs($tab['except'])
                : request()->routeIs($tab['match']);
        @endphp
        <a href="{{ route($tab['route']) }}"
           class="{{ $active ? 'text-[var(--yamaha-red)] font-medium' : 'text-gray-600 hover:text-black' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
