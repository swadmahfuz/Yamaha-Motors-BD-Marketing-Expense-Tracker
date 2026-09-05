<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:categories,code'],
        ]);

        $category = Category::create($data + ['is_active' => true]);
        $audit->log('category.created', $category, null, $data);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:categories,code,'.$category->id],
            'is_active' => ['boolean'],
        ]);

        $old = $category->only(['name', 'code', 'is_active']);
        $category->update($data);
        $audit->log('category.updated', $category, $old, $data);

        return back()->with('success', 'Category updated.');
    }
}
