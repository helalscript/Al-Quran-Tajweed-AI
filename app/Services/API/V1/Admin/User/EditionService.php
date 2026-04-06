<?php

namespace App\Services\API\V1\Admin\User;

use App\Models\Edition;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditionService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function getTranslationEditions($request)
    {
        try {
            $per_page = $request->per_page ?? 25;
            $userLanguage = $this->user->language_code??'en';
            $editions = Edition::where('format', 'text')->where('language', $userLanguage)->paginate($per_page);
            return $editions;
        } catch (Exception $e) {
            Log::error('EditionService::getAllTextEditions'.$e->getMessage());
            throw $e;
        }
    }

    public function getRecitationEditions($request)
    {
        try {
            $per_page = $request->per_page ?? 25;
            $editions = Edition::where('format', 'audio')->paginate($per_page);
            return $editions;
        } catch (Exception $e) {
            Log::error('EditionService::getRecitationEditions'.$e->getMessage());
            throw $e;
        }
    }
}
