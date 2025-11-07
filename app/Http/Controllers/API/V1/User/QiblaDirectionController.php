<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\QiblaDirectionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QiblaDirectionController extends Controller
{

    public function __construct(protected QiblaDirectionService $qiblaDirectionService)
    {
        //
    }
    /**
     * Display a listing of the resource.
     */
    public function getDirection(Request $request)
    {
        $validatedData = request()->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        try {
            $direction = $this->qiblaDirectionService->getDirection($validatedData);
            return Helper::jsonResponse(true, 'Qibla direction fetched successfully', 200, $direction);
        } catch (Exception $e) {
            Log::error('QiblaDirectionController::getDirection: ' . $e->getMessage());
            return Helper::jsonErrorResponse('Failed to fetch Qibla direction', 500);
        }

    }


}
