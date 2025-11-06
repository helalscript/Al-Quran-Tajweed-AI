<?php

namespace App\Services\API\V1\User;

use App\Models\AppLanguage;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppLanguageService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }
    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $per_page = $request->per_page ?? 25;
            $appLanguages = AppLanguage::select('id', 'name', 'code', 'flag_icon')
                ->where('status', 'active')
                ->paginate($per_page);
            return $appLanguages;
        } catch (Exception $e) {
            Log::error("AppLanguageService::index" . $e->getMessage());
            throw $e;
        }
    }

    public function setLanguage($validateData)
    {
        try {
            $languageCode = $validateData['language_code'] ?? $this->user->language_code;
            $language = AppLanguage::where('code', $languageCode)->first();

            if (!$language) {
                throw new Exception('Language not found.');
            }

            $this->user->language_code = $languageCode;
            $this->user->save();

            return $language;
        } catch (Exception $e) {
            Log::error("AppLanguageService::setLanguage" . $e->getMessage());
            throw $e;
        }
    }

}