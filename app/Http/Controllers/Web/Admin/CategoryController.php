<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class CategoryController extends BaseAdminResourceController
{
    protected function resourceConfig(): array
    {
        return [
            'model' => Category::class,
            'name' => 'categories',
            'title' => 'Categories',
            'index_view' => 'admin/categories/index',
            'columns' => [
                'image' => [
                    'label' => 'Image',
                    'type' => 'image',
                    'searchable' => false,
                    'filterable' => false,
                    'visible' => true,
                ],
                'name' => [
                    'label' => 'Name',
                    'searchable' => true,
                ],
                'type' => [
                    'label' => 'Type',
                    'searchable' => true,
                ],
                'order' => [
                    'label' => 'Order',
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'status',
                ],
            ],
            'status_column' => 'status',
            'status_active' => 'active',
            'status_inactive' => 'inactive',
            'allowed_sorts' => ['name', 'type', 'order', 'created_at'],
            'order_by' => ['order', 'asc'],
        ];
    }

    public function create(): Response
    {
        return Inertia::render('admin/categories/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:dua,quran,prayer'],
            'order' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['translations'] = []; // Keep empty array for now

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = 'storage/' . $imagePath;
        }

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('admin/categories/edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'order' => $category->order,
                'status' => $category->status,
                'image' => $category->image,
            ],
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:dua,quran,prayer'],
            'order' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($category->image) {
                $oldPath = str_replace('storage/', '', $category->image);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = 'storage/' . $imagePath;
        } else {
            unset($validated['image']);
        }

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        return $this->destroyModel($category);
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        return $this->toggleModelStatus($category);
    }
}
