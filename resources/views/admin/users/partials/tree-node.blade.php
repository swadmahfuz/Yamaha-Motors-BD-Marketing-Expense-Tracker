@php
    $children = $allUsers->where('manager_id', $user->id)->sortBy('name');
@endphp

<li>
    <div class="flex flex-wrap items-center gap-2" style="padding-left: {{ $depth * 1.25 }}rem">
        <span class="font-medium">{{ $user->name }}</span>
        @foreach ($user->roles as $role)
            <span class="badge badge-gray">{{ str_replace('_', ' ', $role->name) }}</span>
        @endforeach
        @unless ($user->is_active)
            <span class="badge badge-red">Inactive</span>
        @endunless
        <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-[var(--yamaha-red)] uppercase tracking-wide">Edit</a>
    </div>
    @if ($children->isNotEmpty())
        <ul class="mt-2 space-y-2">
            @foreach ($children as $child)
                @include('admin.users.partials.tree-node', ['user' => $child, 'depth' => $depth + 1, 'allUsers' => $allUsers])
            @endforeach
        </ul>
    @endif
</li>
