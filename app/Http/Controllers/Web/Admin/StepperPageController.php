<?php

namespace App\Http\Controllers\Web\Admin;

use App\Facades\DataTable;
use App\Models\StepperPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StepperPageController extends BaseAdminResourceController
{
    protected function resourceConfig(): array
    {
        return [
            'name' => 'stepper-pages',
            'title' => 'Stepper Pages',
            'model' => StepperPage::class,
            'index_view' => 'admin/stepper-pages/index',
            'order_by' => ['order_no', 'asc'],
            'allowed_sorts' => ['title', 'order_no'],
            'status_column' => 'status',
            'status_active' => 'active',
            'status_inactive' => 'inactive',
            'columns' => [
                'title' => [
                    'label' => 'Title',
                    'searchable' => true,
                    'filterable' => false,
                    'visible' => true,
                ],
                'description' => [
                    'label' => 'Description',
                    'searchable' => true,
                    'filterable' => false,
                    'visible' => false,
                ],
                'image' => [
                    'label' => 'Image',
                    'type' => 'image',
                    'searchable' => false,
                    'filterable' => false,
                    'visible' => true,
                ],
                'order_no' => [
                    'label' => 'Order No',
                    'searchable' => false,
                    'filterable' => false,
                    'visible' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'status',
                    'searchable' => false,
                    'filterable' => true,
                    'visible' => true,
                ],
            ],
        ];
    }

    public function create(): Response
    {
        return Inertia::render('admin/stepper-pages/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order_no' => 'required|integer|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stepper-pages', 'public');
            $validated['image'] = 'storage/'.$imagePath;
        } else {
            unset($validated['image']);
        }

        StepperPage::create($validated);

        return redirect()->route('admin.stepper-pages.index')->with('success', 'Stepper page created successfully.');
    }

    public function show(StepperPage $stepper_page): Response
    {
        return Inertia::render('admin/stepper-pages/show', [
            'page' => $stepper_page,
        ]);
    }

    public function edit(StepperPage $stepper_page): Response
    {
        return Inertia::render('admin/stepper-pages/edit', [
            'page' => $stepper_page,
        ]);
    }

    public function update(Request $request, StepperPage $stepper_page): RedirectResponse
    {
   
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order_no' => 'required|integer|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stepper-pages', 'public');
            $validated['image'] = 'storage/'.$imagePath;
        } else {
            unset($validated['image']);
        }

        $stepper_page->update($validated);

        return redirect()->route('admin.stepper-pages.index')->with('success', 'Stepper page updated successfully.');
    }

    public function destroy(StepperPage $stepper_page): RedirectResponse
    {
        return $this->destroyModel($stepper_page)->with('success', 'Stepper page deleted successfully.');
    }

    public function toggleStatus(StepperPage $stepper_page, Request $request): RedirectResponse
    {
        return $this->toggleModelStatus($stepper_page);
    }

    public function newIndex(Request $request): Response
    {
        $sort = str_replace(
            ['name'],
            ['name'],
            request()->query('col')
        );

        $result = DataTable::query(StepperPage::query())
            ->searchable(['name'])
            ->applySort($sort)
            ->allowedSorts(['name'])
            ->make();

        return Inertia::render('admin/stepper-pages/partials/index', [
            'pages' => $result,
        ]);
    }
}
