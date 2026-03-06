<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends BaseAdminResourceController
{
    protected function resourceConfig(): array
    {
        return [
            'name' => 'users',
            'title' => 'Users',
            'model' => User::class,
            'index_view' => 'admin/users/index',
            'order_by' => ['created_at', 'desc'],
            'allowed_sorts' => ['name', 'email', 'created_at'],
            'status_column' => 'status',
            'status_active' => 'active',
            'status_inactive' => 'inactive',
            'columns' => [
                'avatar' => [
                    'label' => 'Avatar',
                    'type' => 'image',
                    'searchable' => false,
                    'filterable' => false,
                    'visible' => true,
                ],
                'name' => [
                    'label' => 'Name',
                    'searchable' => true,
                    'filterable' => false,
                    'visible' => true,
                ],
                'email' => [
                    'label' => 'Email',
                    'searchable' => true,
                    'filterable' => false,
                    'visible' => true,
                ],
                'created_at' => [
                    'label' => 'Joined At',
                    'type' => 'datetime',
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

    public function show(User $user): Response
    {
        return Inertia::render('admin/users/show', [
            'user' => $user,
        ]);
    }

    public function toggleStatus(User $user, Request $request): RedirectResponse
    {
        return $this->toggleModelStatus($user);
    }
}
