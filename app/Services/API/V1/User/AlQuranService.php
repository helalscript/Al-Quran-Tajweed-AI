<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlQuranService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function getAllSurahs()
    {
        try {
            return Cache::remember('all_surahs', now()->addMonth(), function () {
                $apiUrl = "https://api.alquran.cloud/v1/surah";
                $response = Http::get($apiUrl);
    
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception("Failed to fetch surahs from external API.");
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getAllSurahs ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSurahByNumber(string $number,$editions="'quran-uthmani','ar.alafasy','bn.bengali'"): mixed
    {
        try {
        
            return Cache::remember('surah_' . $number.'_'. $editions, now()->addMonth(), function () use ($number, $editions) {
                $apiUrl = "https://api.alquran.cloud/v1/surah/" . $number ."/editions/" . $editions;
                $response = Http::get($apiUrl);
    
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception("Failed to fetch surah from external API.");
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getSurahByNumber'.$e->getMessage());
            throw $e;
        }
    }
}
