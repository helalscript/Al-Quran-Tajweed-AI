<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\DuaDhikir;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DuaDhikirController extends BaseAdminResourceController
{
    protected function resourceConfig(): array
    {
        return [
            'name' => 'dua-dhikir',
            'title' => 'Dua & Dhikir',
            'model' => DuaDhikir::class,
            'index_view' => 'admin/dua-dhikir/index',
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
                'category' => [
                    'label' => 'Category',
                    'searchable' => true,
                    'filterable' => true,
                    'visible' => true,
                ],
                'image' => [
                    'label' => 'Image',
                    'type' => 'image',
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

    public function destroy(DuaDhikir $dua_dhikir): RedirectResponse
    {
        return $this->destroyModel($dua_dhikir);
    }

    public function toggleStatus(DuaDhikir $dua_dhikir, Request $request): RedirectResponse
    {
        return $this->toggleModelStatus($dua_dhikir);
    }
}
