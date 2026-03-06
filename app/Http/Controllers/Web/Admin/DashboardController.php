<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\StepperPage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function __invoke(): Response
    {
        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('status', 'active')->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        $totalStepperPages = StepperPage::query()->count();
        $activeStepperPages = StepperPage::query()->where('status', 'active')->count();

        $recentUsers = User::query()
            ->select('id', 'name', 'email', 'created_at')
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
            ],
            'stepper_pages' => [
                'total' => $totalStepperPages,
                'active' => $activeStepperPages,
            ],
        ];

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
        ]);
    }
}
