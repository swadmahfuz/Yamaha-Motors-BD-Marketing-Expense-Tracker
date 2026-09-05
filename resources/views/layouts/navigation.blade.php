<nav x-data="{ open: false }" class="bg-[var(--yamaha-black)] border-b border-[var(--yamaha-silver)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 shrink-0">
                    <img src="{{ asset('images/yamaha-logo.jpg') }}" alt="Yamaha" class="h-8 w-auto bg-white px-1">
                    <span class="text-white text-sm font-semibold uppercase tracking-widest hidden sm:inline">Expense Tracker</span>
                </a>

                <div class="hidden sm:flex sm:items-center sm:gap-6">
                    <a href="{{ route('dashboard') }}" class="text-sm uppercase tracking-wide {{ request()->routeIs('dashboard') ? 'text-[var(--yamaha-red)]' : 'text-gray-300 hover:text-white' }}">Dashboard</a>
                    @can('create', App\Models\BudgetRequest::class)
                        <a href="{{ route('requests.index') }}" class="text-sm uppercase tracking-wide {{ request()->routeIs('requests.*') ? 'text-[var(--yamaha-red)]' : 'text-gray-300 hover:text-white' }}">Requests</a>
                    @endcan
                    @if(auth()->user()->canAccessApprovals())
                        <a href="{{ route('approvals.index') }}" class="text-sm uppercase tracking-wide {{ request()->routeIs('approvals.*') ? 'text-[var(--yamaha-red)]' : 'text-gray-300 hover:text-white' }}">Approvals</a>
                    @endif
                    @role('head_of_marketing|admin|super_admin')
                        <a href="{{ route('hom.dashboard') }}" class="text-sm uppercase tracking-wide {{ request()->routeIs('hom.*') ? 'text-[var(--yamaha-red)]' : 'text-gray-300 hover:text-white' }}">HoM</a>
                    @endrole
                    @role('super_admin')
                        <a href="{{ route('super-admin.backdates') }}" class="text-sm uppercase tracking-wide {{ request()->routeIs('super-admin.*') ? 'text-[var(--yamaha-red)]' : 'text-gray-300 hover:text-white' }}">Backdates</a>
                    @endrole
                    @role('admin|super_admin|head_of_marketing')
                        <a href="{{ route('admin.users.index') }}" class="text-sm uppercase tracking-wide {{ request()->routeIs('admin.*') ? 'text-[var(--yamaha-red)]' : 'text-gray-300 hover:text-white' }}">Admin</a>
                    @endrole
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:gap-4">
                <a href="{{ route('notifications.index') }}" class="text-gray-300 hover:text-white text-sm">
                    Notifications
                    @if(auth()->user()->unreadNotifications->count())
                        <span class="badge badge-red ml-1">{{ auth()->user()->unreadNotifications->count() }}</span>
                    @endif
                </a>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm text-gray-300 hover:text-white">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[var(--yamaha-black)] pb-4">
        <div class="pt-2 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block py-2 text-gray-300">Dashboard</a>
            @can('create', App\Models\BudgetRequest::class)
                <a href="{{ route('requests.index') }}" class="block py-2 text-gray-300">Requests</a>
            @endcan
            @if(auth()->user()->canAccessApprovals())
                <a href="{{ route('approvals.index') }}" class="block py-2 text-gray-300">Approvals</a>
            @endif
            @role('head_of_marketing|admin|super_admin')
                <a href="{{ route('hom.dashboard') }}" class="block py-2 text-gray-300">HoM Dashboard</a>
            @endrole
            @role('admin|super_admin|head_of_marketing')
                <a href="{{ route('admin.users.index') }}" class="block py-2 text-gray-300">Admin</a>
            @endrole
            @role('super_admin')
                <a href="{{ route('super-admin.backdates') }}" class="block py-2 text-gray-300">Backdates</a>
            @endrole
        </div>
    </div>
</nav>
