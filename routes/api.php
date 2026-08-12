<?php

use App\Http\Controllers\API\V1\Admin\System\DynamicPageController;
use App\Http\Controllers\API\V1\Admin\SystemSettingController;
use App\Http\Controllers\API\V1\Auth\LoginController;
use App\Http\Controllers\API\V1\Auth\LogoutController;
use App\Http\Controllers\API\V1\Auth\RegisterController;
use App\Http\Controllers\API\V1\Auth\ResetPasswordController;
use App\Http\Controllers\API\V1\Auth\SocialLoginController;
use App\Http\Controllers\API\V1\Auth\UserController;
use App\Http\Controllers\API\V1\Public\GeneralSettingController;
use App\Http\Controllers\API\V1\Public\PackageController;
use App\Http\Controllers\API\V1\Public\StepperPageController as PublicStepperPageController;
use App\Http\Controllers\API\V1\User\AlQuranController;
use App\Http\Controllers\API\V1\User\AppDisplaySettingsController;
use App\Http\Controllers\API\V1\User\AppLanguageController;
use App\Http\Controllers\API\V1\User\CategoryController;
use App\Http\Controllers\API\V1\User\DuaDhikirController;
use App\Http\Controllers\API\V1\User\EditionController;
use App\Http\Controllers\API\V1\User\FavouriteController;
use App\Http\Controllers\API\V1\User\MemorizationController;
use App\Http\Controllers\API\V1\User\NotificationController;
use App\Http\Controllers\API\V1\User\PrayerTimeController;
use App\Http\Controllers\API\V1\User\QiblaDirectionController;
use App\Http\Controllers\API\V1\User\QuranReadingHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth');

Route::group(['middleware' => ['guest:api']], function ($router) {
    // Route::group(['middleware' => ['guest:api', 'throttle:2,1']], function ($router) {
    // register
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']);
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
    // login
    Route::post('login', [LoginController::class, 'login']);
    // forgot password
    Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
    // social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
});

Route::group(['middleware' => 'auth:api'], function ($router) {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/update-password', [UserController::class, 'changePassword']);
    Route::delete('/delete-profile', [UserController::class, 'deleteProfile']);
});

// this is for user
Route::group(['middleware' => ['auth:api', 'is_user']], function ($router) {

    // prayer times
    Route::post('prayer-times-new', [PrayerTimeController::class, 'getSingaporeOrLocalPrayerTimes']);
    Route::post('prayer-times', [PrayerTimeController::class, 'getPrayerTimes']);
    //prayer times with countries
    Route::post('prayer-times-countries', [PrayerTimeController::class, 'getPrayerTimesWithCountries']);
    // prayer time notification settings
    Route::get('prayer-time-notification-settings', [PrayerTimeController::class, 'getPrayerTimeNotificationSettings']);
    Route::put('update-prayer-time-notification-settings', [PrayerTimeController::class, 'updatePrayerTimeNotificationSettings']);

    // qibla direction
    Route::post('qibla-direction', [QiblaDirectionController::class, 'getDirection']);

    // al quran
    Route::group(['prefix' => 'al-quran'], function () {
        Route::get('surahs', [AlQuranController::class, 'getAllSurahs']);
        Route::get('surahs/language', [AlQuranController::class, 'getAllSurahsByUserLanguage']);
        Route::get('surahs/{number}/editions/{editions?}', [AlQuranController::class, 'getSurahByNumber']);
        Route::get('surah/tajweed/{number}', [AlQuranController::class, 'showTajweedSurah']);
        Route::get('juzs', [AlQuranController::class, 'getAllJuzs']);
        Route::get('juzs/{number}', [AlQuranController::class, 'getJuzByNumber']);
        Route::get('search', [AlQuranController::class, 'search']);

        Route::post('history', [QuranReadingHistoryController::class, 'saveLastRead']);
        Route::get('history', [QuranReadingHistoryController::class, 'getLastRead']);
    });

    // notifications
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show']);
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/delete-all', [NotificationController::class, 'deleteAll']);

    // app languages
    Route::get('app-languages', [AppLanguageController::class, 'index']);
    Route::post('set-language', [AppLanguageController::class, 'setLanguage']);

    // app display settings
    Route::get('display-settings', [AppDisplaySettingsController::class, 'getDisplaySettings']);
    Route::put('display-settings', [AppDisplaySettingsController::class, 'updateDisplaySettings']);

    // memorization
    Route::post('memorization/sessions/start', [MemorizationController::class, 'startSession']);
    Route::get('memorization/sessions/{id}', [MemorizationController::class, 'getSession']);
    Route::put('memorization/sessions/{id}/end', [MemorizationController::class, 'endSession']);
    Route::post('memorization/sessions/{id}/recite', [MemorizationController::class, 'streamRecitation']);
    Route::get('memorization/history', [MemorizationController::class, 'getHistory']);
    Route::get('memorization/sessions/{id}/mistakes', [MemorizationController::class, 'getMistakes']);

    // dua categories
    Route::get('dua/categories', [CategoryController::class, 'index']);
    Route::get('dua/categories/{id}', [CategoryController::class, 'show']);
    Route::get('dua/categories/slug/{slug}', [CategoryController::class, 'getBySlug']);

    // dua dhikrs
    Route::get('dua/categories/{categoryId}/duas', [DuaDhikirController::class, 'getByCategory']);
    Route::get('dua/categories/slug/{slug}/duas', [DuaDhikirController::class, 'getByCategorySlug']);
    Route::get('dua/search', [DuaDhikirController::class, 'search']);
    Route::get('dua/{id}', [DuaDhikirController::class, 'show']);

    // favourites
    Route::get('favourites', [FavouriteController::class, 'index']);
    Route::post('favourites', [FavouriteController::class, 'store']);
    Route::post('favourites/toggle', [FavouriteController::class, 'toggle']);
    Route::delete('favourites/{id}', [FavouriteController::class, 'destroy']);

    // editions
    Route::get('editions-translation', [EditionController::class, 'getTranslationEditions']);
    Route::get('editions-recitation', [EditionController::class, 'getRecitationEditions']);
    
    // subscriptions
    Route::post('subscriptions/sync', [\App\Http\Controllers\API\V1\User\SubscriptionController::class, 'syncSubscription']);
    Route::get('subscriptions/status', [\App\Http\Controllers\API\V1\User\SubscriptionController::class, 'checkStatus']);
});

// this is for admin
// Route::group(['middleware' => ['auth:api', 'is_admin'], 'prefix' => 'admin'], function ($router) {
//     Route::apiResource('dynamic-pages', DynamicPageController::class);
//     Route::group(['prefix' => 'system'], function ($router) {
//         Route::get('settings', [SystemSettingController::class, 'index']);
//         Route::post('settings', [SystemSettingController::class, 'update']);
//         Route::get('mail-settings', [SystemSettingController::class, 'getMailSetting']);
//         Route::post('mail-settings', [SystemSettingController::class, 'updateMailSetting']);
//         Route::get('clear-cache', [SystemSettingController::class, 'clearCache']);
//     });
// });

// this is for public
Route::get('system-info', [GeneralSettingController::class, 'getSystemInfo']);
Route::get('dynamic-pages', [GeneralSettingController::class, 'getDynamicPages']);
Route::get('dynamic-pages/{page_slug}', [GeneralSettingController::class, 'showDaynamicPage']);
Route::get('faqs', [GeneralSettingController::class, 'getFaqs']);
Route::apiResource('packages', PackageController::class)->only(['index', 'show']);
Route::apiResource('stepper-pages', PublicStepperPageController::class)->only(['index', 'show']);

// RevenueCat Webhook
Route::post('webhook/revenuecat', [\App\Http\Controllers\API\V1\Webhook\RevenueCatWebhookController::class, 'handle']);

//  require __DIR__ . '/api_v1.php';
