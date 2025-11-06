<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\AppLanguageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppLanguageController extends Controller
{
    public function __construct(protected AppLanguageService $appLanguageService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $languages = $this->appLanguageService->index($request);
            return Helper::jsonResponse(true, 'Languages retrieved successfully.', 200, $languages, true);
        } catch (Exception $e) {
            Log::error("AppLanguageController index error: " . $e->getMessage());
            return Helper::jsonResponse(false, 'Failed to retrieve languages.', 500);
        }
    }

    public function setLanguage(Request $request)
    {
        $validateData = $request->validate([
            'language_code' => 'required|string|exists:app_languages,code',
        ]);

        try {
            $language = $this->appLanguageService->setLanguage($validateData);
            return Helper::jsonResponse(true, 'Language set successfully.', 200);
        } catch (Exception $e) {
            Log::error("AppLanguageController setLanguage error: " . $e->getMessage());
            return Helper::jsonResponse(false, 'Failed to set language.', 500);
        }
    }
}
