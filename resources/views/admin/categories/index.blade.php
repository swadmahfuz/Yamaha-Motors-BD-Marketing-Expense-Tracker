<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Categories</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin-nav />

        <form method="POST" action="{{ route('admin.categories.store') }}" class="card space-y-3">
            @csrf
            <h3 class="font-semibold text-sm uppercase tracking-wide">Add category</h3>
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
                    @foreach($categories as $category)
                        <tr>
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                                @csrf @method('PATCH')
                                <td><input name="name" value="{{ $category->name }}" class="input-field"></td>
                                <td><input name="code" value="{{ $category->code }}" class="input-field"></td>
                                <td><input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}></td>
                                <td><button class="btn-secondary text-xs">Save</button></td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
