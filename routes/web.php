<?php

use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\DuaDhikirController;
use App\Http\Controllers\Web\Admin\DynamicPageController;
use App\Http\Controllers\Web\Admin\StepperPageController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');
// Route::get('/', function () {
//     return redirect()->route('login');
// });

Route::get('/page/{dynamic_page:page_slug}', [PageController::class, 'show'])
    ->name('page.show');

Route::middleware(['auth', 'role_check'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('stepper-pages', StepperPageController::class);
        Route::patch('stepper-pages/{stepper_page}/toggle', [StepperPageController::class, 'toggleStatus'])->name('stepper-pages.toggle');
        Route::get('stepper-data', [StepperPageController::class, 'newIndex'])->name('stepper-data');

        Route::resource('dua-dhikir', DuaDhikirController::class)->parameters(['dua-dhikir' => 'dua_dhikir']);
        Route::patch('dua-dhikir/{dua_dhikir}/toggle', [DuaDhikirController::class, 'toggleStatus'])->name('dua-dhikir.toggle');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

        Route::resource('pages', DynamicPageController::class)->except(['show']);
        Route::patch('pages/{page}/toggle', [DynamicPageController::class, 'toggleStatus'])->name('pages.toggle');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

