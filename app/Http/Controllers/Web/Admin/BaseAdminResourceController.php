<?php

namespace App\Http\Controllers\Web\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

abstract class BaseAdminResourceController
{
    abstract protected function resourceConfig(): array;

    public function index(Request $request): Response
    {
        $config = $this->resourceConfig();

        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];
        $query = $modelClass::query();

        $this->applySearch($query, $request, $config);
        $this->applyFilters($query, $request, $config);

        $this->applySorting($query, $request, $config);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query
            ->paginate($request->integer('per_page', 10))
            ->appends($request->query());

        return Inertia::render($config['index_view'], [
            'title' => $config['title'],
            'resource' => $config['name'],
            'columns' => $config['columns'],
            'items' => $paginator,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'per_page' => $request->integer('per_page', 10),
                'sort_col' => $request->string('sort_col')->toString(),
                'sort_dir' => $request->string('sort_dir')->toString(),
            ],
        ]);
    }

    protected function destroyModel(Model $model): RedirectResponse
    {
        $model->delete();

        return back();
    }

    protected function toggleModelStatus(Model $model): RedirectResponse
    {
        $statusColumn = $this->resourceConfig()['status_column'] ?? 'status';
        $active = $this->resourceConfig()['status_active'] ?? 'active';
        $inactive = $this->resourceConfig()['status_inactive'] ?? 'inactive';

        $model->{$statusColumn} = $model->{$statusColumn} === $active ? $inactive : $active;
        $model->save();

        return back()->with('success', 'Status updated successfully.');
    }

    protected function applySearch(Builder $query, Request $request, array $config): void
    {
        $search = $request->string('search')->trim()->toString();

        if ($search === '') {
            return;
        }

        $searchable = collect($config['columns'])
            ->filter(static fn ($col) => ($col['searchable'] ?? true) === true)
            ->keys()
            ->all();

        if ($searchable === []) {
            return;
        }

        $query->where(function (Builder $q) use ($search, $searchable): void {
            foreach ($searchable as $column) {
                $q->orWhere($column, 'like', '%'.$search.'%');
            }
        });
    }

    protected function applyFilters(Builder $query, Request $request, array $config): void
    {
        $status = $request->string('status')->trim()->toString();

        if ($status !== '') {
            $statusColumn = $config['status_column'] ?? 'status';

            $query->where($statusColumn, $status);
        }
    }

    protected function applySorting(Builder $query, Request $request, array $config): void
    {
        $allowed = $config['allowed_sorts'] ?? [];
        $sortCol = $request->string('sort_col')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir')->trim()->toString());

        if ($sortCol !== '' && in_array($sortCol, $allowed, true) && in_array($sortDir, ['asc', 'desc'], true)) {
            $query->orderBy($sortCol, $sortDir);

            return;
        }

        $orderBy = $config['order_by'] ?? null;
        if (is_array($orderBy)) {
            $query->orderBy($orderBy[0], $orderBy[1] ?? 'asc');

            return;
        }

        $query->latest();
    }
}
