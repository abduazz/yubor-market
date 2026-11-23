<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'section' => ['nullable', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:100', 'unique:categories,slug'],
            'image' => ['nullable', 'string', 'max:255'],
            'demo_count' => ['required', 'integer', 'min:1', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Category::query()->create([
            'title' => $data['title'],
            'section' => $data['section'] ?: $data['title'],
            'slug' => Str::slug($data['slug']),
            'image' => $data['image'] ?: 'img/cat-phones.png',
            'demo_count' => (int) $data['demo_count'],
            'sort_order' => (int) ($data['sort_order'] ?? (Category::query()->max('sort_order') + 1)),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('admin.flash.category_created', ['title' => $data['title']]));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'section' => ['nullable', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('categories', 'slug')->ignore($category->id)],
            'image' => ['nullable', 'string', 'max:255'],
            'demo_count' => ['required', 'integer', 'min:1', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'title' => $data['title'],
            'section' => $data['section'] ?: $data['title'],
            'slug' => Str::slug($data['slug']),
            'image' => $data['image'] ?: $category->image,
            'demo_count' => (int) $data['demo_count'],
            'sort_order' => (int) ($data['sort_order'] ?? $category->sort_order),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('admin.flash.category_updated', ['title' => $category->title]));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => __('admin.flash.category_has_products', ['title' => $category->title]),
            ]);
        }

        $title = $category->title;
        $category->delete();

        return back()->with('success', __('admin.flash.category_deleted', ['title' => $title]));
    }
}
