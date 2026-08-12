<?php

use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\DuaDhikirController;
use App\Http\Controllers\Web\Admin\DynamicPageController;
use App\Http\Controllers\Web\Admin\StepperPageController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PlayStoreRelatedController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use Illuminate\Foundation\Inspiring;

Route::get('/', function () {
    $quote = Inspiring::quote();
    $cleanQuote = preg_replace('/<[^>]*>/', '', $quote);
    
    return Inertia::render('welcome', [
        'quote' => $cleanQuote,
    ]);
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

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');

        Route::get('al-quran', [\App\Http\Controllers\Web\Admin\AlQuranController::class, 'index'])->name('al-quran.index');
        Route::get('al-quran/page/{page}', [\App\Http\Controllers\Web\Admin\AlQuranController::class, 'showPage'])->name('al-quran.page');
        Route::get('al-quran/{surah}', [\App\Http\Controllers\Web\Admin\AlQuranController::class, 'show'])->name('al-quran.show');
        Route::patch('al-quran/ayah/{ayah_id}/edition/{edition_id}', [\App\Http\Controllers\Web\Admin\AlQuranController::class, 'updateAyah'])->name('al-quran.update-ayah');

        Route::resource('subscriptions', \App\Http\Controllers\Web\Admin\SubscriptionController::class)->only(['index', 'show', 'destroy']);
    });
});

// app related routes
Route::get('/app/auth-user/login', [PlayStoreRelatedController::class, 'showLoginForm']);
Route::post('/app/auth-user/login', [PlayStoreRelatedController::class, 'login'])->name('app.user.login');

Route::get('/app/auth-user/dashboard', [PlayStoreRelatedController::class, 'dashboard'])->name('app.user.dashboard');
Route::post('/app/auth-user/logout', [PlayStoreRelatedController::class, 'destroy'])->name('app.user.logout');
Route::get('/account/delete/confirm/{token}', [PlayStoreRelatedController::class, 'confirmAccountDeletion'])
        ->name('account.delete.confirmation')
        ->middleware('signed');
Route::get('/app/auth-user/delete-account', [PlayStoreRelatedController::class, 'deleteAccount'])
        ->name('app.user.deleteAccount');
Route::get('/account/delete-confirm', [PlayStoreRelatedController::class, 'deleteAccountConfirm'])->name('app.user.deleteAccountConfirm');
Route::get('/confirm/delete/account', [PlayStoreRelatedController::class, 'ConfirmDeleteAccount'])->name('confirm.delete.account');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';



// Route::get('/send-test-mail', function () {
//     try {
//         $to = 'siboxev365@ameady.com';

//         Mail::raw(
//             'Test mail from Laravel + Brevo API. Time: ' . now(),
//             function ($message) use ($to) {
//                 $message->to($to)
//                         ->subject('Laravel Brevo Test — ' . now());
//             }
//         );

//         return response()->json([
//             'status'  => 'success',
//             'message' => 'Email sent to ' . $to,
//             'time'    => now()->toDateTimeString(),
//         ], 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'failed',
//             'error'  => $e->getMessage(),
//         ], 500);
//     }
// });
