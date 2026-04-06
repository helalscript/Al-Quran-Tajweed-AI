<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Admin\User\EditionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EditionController extends Controller
{
    public function __construct(protected EditionService $editionService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function getTranslationEditions(Request $request)
    {
        try {
            $editions = $this->editionService->getTranslationEditions($request);
            return Helper::jsonResponse(true, 'Translation editions fetched successfully', 200, $editions,true);
        } catch (Exception $e) {
            Log::error('EditionController::getTranslationEditions'.$e->getMessage());
            return Helper::jsonErrorResponse('Failed to fetch translation editions', 500);
        }
    }

    public function getRecitationEditions(Request $request)
    {
        try {
            $editions = $this->editionService->getRecitationEditions($request);
            return Helper::jsonResponse(true, 'Recitation editions fetched successfully', 200, $editions,true);
        } catch (Exception $e) {
            Log::error('EditionController::getRecitationEditions'.$e->getMessage());
            return Helper::jsonErrorResponse('Failed to fetch recitation editions', 500);
        }
    }
}
