<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\AlQuranService;
use Exception;
use Illuminate\Support\Facades\Log;

class AlQuranController extends Controller
{
    public function __construct(protected AlQuranService $alQuranService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function getAllSurahs()
    {
        try {
            $surahs = $this->alQuranService->getAllSurahs();

            return Helper::jsonResponse(true, 'Surahs fetched successfully', 200, $surahs);
        } catch (Exception $e) {
            Log::error('AlQuranController::getAllSurahs'.$e->getMessage());

            return Helper::jsonErrorResponse('Failed to fetch surahs', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getSurahByNumber(string $number,$editions="'quran-uthmani','ar.alafasy','bn.bengali'")
    {
        try {
            $surah = $this->alQuranService->getSurahByNumber($number,$editions);

            return Helper::jsonResponse(true, 'Surah fetched successfully', 200, $surah);
        } catch (Exception $e) {
            Log::error('AlQuranController::getSurahByNumber'.$e->getMessage());

            return Helper::jsonErrorResponse('Failed to fetch surah', 500);
        }
    }
}
