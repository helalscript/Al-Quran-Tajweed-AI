<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\DynamicPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DynamicPageController extends BaseAdminResourceController
{
    protected function resourceConfig(): array
    {
        return [
            'model' => DynamicPage::class,
            'name' => 'pages',
            'title' => 'Dynamic Pages',
            'index_view' => 'admin/dynamic-pages/index',
            'columns' => [
                'page_title' => [
                    'label' => 'Title',
                    'searchable' => true,
                ],
                'page_slug' => [
                    'label' => 'Slug',
                    'searchable' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'status',
                ],
            ],
            'status_column' => 'status',
            'status_active' => 'active',
            'status_inactive' => 'inactive',
            'allowed_sorts' => ['page_title', 'created_at'],
            'order_by' => ['created_at', 'desc'],
        ];
    }

    public function create(): Response
    {
        return Inertia::render('admin/dynamic-pages/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_title' => ['required', 'string', 'max:255'],
            'page_content' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['page_slug'] = generateUniqueSlug($validated['page_title']);

        DynamicPage::create($validated);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(DynamicPage $page): Response
    {
        return Inertia::render('admin/dynamic-pages/edit', [
            'page' => [
                'id' => $page->id,
                'page_title' => $page->page_title,
                'page_slug' => $page->page_slug,
                'page_content' => $page->page_content,
                'status' => $page->status,
            ],
        ]);
    }

    public function update(Request $request, DynamicPage $page): RedirectResponse
    {
        $validated = $request->validate([
            'page_title' => ['required', 'string', 'max:255'],
            'page_content' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['page_slug'] = generateUniqueSlug($validated['page_title'], $page->id);

        $page->update($validated);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(DynamicPage $page): RedirectResponse
    {
        return $this->destroyModel($page);
    }

    public function toggleStatus(DynamicPage $page): RedirectResponse
    {
        return $this->toggleModelStatus($page);
    }
}

