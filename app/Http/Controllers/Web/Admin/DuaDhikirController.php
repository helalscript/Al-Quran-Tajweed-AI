<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\Category;
use App\Models\DuaDhikir;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DuaDhikirController extends BaseAdminResourceController
{
    protected function resourceConfig(): array
    {
        return [
            'name'            => 'dua-dhikir',
            'title'           => 'Dua & Dhikir',
            'model'           => DuaDhikir::class,
            'index_view'      => 'admin/dua-dhikir/index',
            'status_column'   => 'status',
            'status_active'   => 'active',
            'status_inactive' => 'inactive',
            'order_by'        => ['order', 'asc'],
            'columns'         => [
                'title'    => [
                    'label'      => 'Title',
                    'searchable' => true,
                    'filterable' => false,
                    'visible'    => true,
                ],
                'category' => [
                    'label'      => 'Category',
                    'searchable' => false,
                    'filterable' => false,
                    'visible'    => true,
                ],
                'image'    => [
                    'label'      => 'Image',
                    'type'       => 'image',
                    'searchable' => false,
                    'filterable' => false,
                    'visible'    => true,
                ],
                'status'   => [
                    'label'      => 'Status',
                    'type'       => 'status',
                    'searchable' => false,
                    'filterable' => true,
                    'visible'    => true,
                ],
            ],
        ];
    }

    protected function applyFilters(\Illuminate\Database\Eloquent\Builder $query, Request $request, array $config): void
    {
        parent::applyFilters($query, $request, $config);

        $categoryId = $request->string('category_id')->trim()->toString();
        if ($categoryId !== '') {
            $query->where('category_id', $categoryId);
        }
    }

    protected function applySearch(\Illuminate\Database\Eloquent\Builder $query, Request $request, array $config): void
    {
        $search = $request->string('search')->trim()->toString();

        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->whereHas('translations', function ($q2) use ($search) {
                $q2->where('title', 'like', '%' . $search . '%')
                   ->orWhere('translation', 'like', '%' . $search . '%');
            })
            ->orWhere('arabic', 'like', '%' . $search . '%')
            ->orWhereHas('category', function ($q2) use ($search) {
                $q2->where('name', 'like', '%' . $search . '%');
            });
        });
    }

    /** GET /admin/dua-dhikir — override to eager-load category and translations */
    public function index(\Illuminate\Http\Request $request): \Inertia\Response
    {
        $config = $this->resourceConfig();

        $query = DuaDhikir::with(['category', 'translations']);
        $this->applySearch($query, $request, $config);
        $this->applyFilters($query, $request, $config);
        $this->applySorting($query, $request, $config);

        $paginator = $query
            ->paginate($request->integer('per_page', 10))
            ->appends($request->query());

        // Map items so we display one translated title
        $paginator->getCollection()->transform(function (DuaDhikir $item) {
            $mainTranslation = $item->translations->firstWhere('language_code', 'en') 
                               ?? $item->translations->first();

            return [
                'id'       => $item->id,
                'title'    => $mainTranslation ? $mainTranslation->title : 'No Title',
                'category' => $item->category?->name,
                'image'    => $item->getRawOriginal('image')
                              ? Storage::disk('public')->url($item->getRawOriginal('image'))
                              : null,
                'status'   => $item->status,
            ];
        });

        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return \Inertia\Inertia::render($config['index_view'], [
            'title'    => $config['title'],
            'resource' => $config['name'],
            'columns'  => $config['columns'],
            'items'    => $paginator,
            'categories' => $categories,
            'filters'  => [
                'search'   => $request->string('search')->toString(),
                'status'   => $request->string('status')->toString(),
                'category_id' => $request->string('category_id')->toString(),
                'per_page' => $request->integer('per_page', 10),
                'sort_col' => $request->string('sort_col')->toString(),
                'sort_dir' => $request->string('sort_dir')->toString(),
            ],
        ]);
    }

    /** GET /admin/dua-dhikir/{id} */
    public function show(DuaDhikir $dua_dhikir): Response
    {
        $dua_dhikir->load(['category', 'translations']);

        return Inertia::render('admin/dua-dhikir/show', [
            'item' => [
                'id'            => $dua_dhikir->id,
                'category'      => $dua_dhikir->category?->name,
                'arabic'        => $dua_dhikir->arabic,
                'source'        => $dua_dhikir->source,
                'image_url'     => $dua_dhikir->getRawOriginal('image')
                                    ? Storage::disk('public')->url($dua_dhikir->getRawOriginal('image'))
                                    : null,
                'audio_url'     => $dua_dhikir->audio_url,
                'order'         => $dua_dhikir->order,
                'status'        => $dua_dhikir->status,
            ],
            'translations' => $dua_dhikir->translations,
        ]);
    }

    /** GET /admin/dua-dhikir/create */
    public function create(): Response
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return Inertia::render('admin/dua-dhikir/create', [
            'categories' => $categories,
        ]);
    }

    /** POST /admin/dua-dhikir */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'   => ['required', 'integer', 'exists:categories,id'],
            'arabic'        => ['required', 'string'],
            'source'        => ['nullable', 'string', 'max:255'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'audio_url'     => ['nullable', 'string', 'max:255'],
            'order'         => ['nullable', 'integer'],
            'status'        => ['required', 'in:active,inactive'],
            'translations'  => ['required', 'array', 'min:1'],
            'translations.*.language_code' => ['required', 'string', 'max:10'],
            'translations.*.title'         => ['required', 'string', 'max:255'],
            'translations.*.translation'   => ['nullable', 'string'],
            'translations.*.notes'         => ['nullable', 'string'],
            'translations.*.benefits'      => ['nullable', 'string'],
            'translations.*.fawaid'        => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('dua-dhikir', 'public');
        }

        $validated['order'] = $validated['order'] ?? 0;

        $duaDhikir = DuaDhikir::create([
            'category_id' => $validated['category_id'],
            'arabic'      => $validated['arabic'],
            'source'      => $validated['source'] ?? null,
            'image'       => $validated['image'] ?? null,
            'audio_url   '=> $validated['audio_url'] ?? null,
            'order'       => $validated['order'],
            'status'      => $validated['status'],
        ]);

        foreach ($validated['translations'] as $trans) {
            $duaDhikir->translations()->create($trans);
        }

        return redirect()
            ->route('admin.dua-dhikir.index')
            ->with('success', 'Dua & Dhikir created successfully.');
    }

    /** GET /admin/dua-dhikir/{id}/edit */
    public function edit(DuaDhikir $dua_dhikir): Response
    {
        $dua_dhikir->load('translations');
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return Inertia::render('admin/dua-dhikir/edit', [
            'item'       => [
                'id'            => $dua_dhikir->id,
                'category_id'   => $dua_dhikir->category_id,
                'arabic'        => $dua_dhikir->arabic,
                'source'        => $dua_dhikir->source,
                'image'         => $dua_dhikir->getRawOriginal('image'),
                'image_url'     => $dua_dhikir->getRawOriginal('image')
                                    ? Storage::disk('public')->url($dua_dhikir->getRawOriginal('image'))
                                    : null,
                'audio_url'     => $dua_dhikir->audio_url,
                'order'         => $dua_dhikir->order,
                'status'        => $dua_dhikir->status,
                'translations'  => $dua_dhikir->translations,
            ],
            'categories' => $categories,
        ]);
    }

    /** PUT /admin/dua-dhikir/{id} */
    public function update(Request $request, DuaDhikir $dua_dhikir): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'   => ['required', 'integer', 'exists:categories,id'],
            'arabic'        => ['required', 'string'],
            'source'        => ['nullable', 'string', 'max:255'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'audio_url'     => ['nullable', 'string', 'max:255'],
            'order'         => ['nullable', 'integer'],
            'status'        => ['required', 'in:active,inactive'],
            'translations'  => ['required', 'array', 'min:1'],
            'translations.*.id'            => ['nullable', 'integer', 'exists:dua_translations,id'],
            'translations.*.language_code' => ['required', 'string', 'max:10'],
            'translations.*.title'         => ['required', 'string', 'max:255'],
            'translations.*.translation'   => ['nullable', 'string'],
            'translations.*.notes'         => ['nullable', 'string'],
            'translations.*.benefits'      => ['nullable', 'string'],
            'translations.*.fawaid'        => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists and is stored locally
            $oldImage = $dua_dhikir->getRawOriginal('image');
            if ($oldImage && !filter_var($oldImage, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($oldImage);
            }
            $validated['image'] = $request->file('image')->store('dua-dhikir', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['order'] = $validated['order'] ?? $dua_dhikir->order;

        $dua_dhikir->update([
            'category_id' => $validated['category_id'],
            'arabic'      => $validated['arabic'],
            'source'      => $validated['source'] ?? null,
            'audio_url'   => $validated['audio_url'] ?? null,
            'order'       => $validated['order'],
            'status'      => $validated['status'],
        ] + (isset($validated['image']) ? ['image' => $validated['image']] : []));

        $providedTranslationIds = collect($validated['translations'])->pluck('id')->filter()->toArray();
        
        // Delete translations that are no longer present
        $dua_dhikir->translations()->whereNotIn('id', $providedTranslationIds)->delete();

        foreach ($validated['translations'] as $trans) {
            $dua_dhikir->translations()->updateOrCreate(
                ['id' => $trans['id'] ?? null],
                [
                    'language_code' => $trans['language_code'],
                    'title'         => $trans['title'],
                    'translation'   => $trans['translation'] ?? null,
                    'notes'         => $trans['notes'] ?? null,
                    'benefits'      => $trans['benefits'] ?? null,
                    'fawaid'        => $trans['fawaid'] ?? null,
                ]
            );
        }
        return redirect()
            ->route('admin.dua-dhikir.index')
            ->with('success', 'Dua & Dhikir updated successfully.');
    }

    /** DELETE /admin/dua-dhikir/{id} */
    public function destroy(DuaDhikir $dua_dhikir): RedirectResponse
    {
        // Delete image file if local
        $image = $dua_dhikir->getRawOriginal('image');
        if ($image && !filter_var($image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($image);
        }

        return $this->destroyModel($dua_dhikir);
    }

    /** PATCH /admin/dua-dhikir/{id}/toggle */
    public function toggleStatus(DuaDhikir $dua_dhikir, Request $request): RedirectResponse
    {
        return $this->toggleModelStatus($dua_dhikir);
    }
}
