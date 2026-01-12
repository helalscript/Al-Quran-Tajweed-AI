<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\DuaDhikrService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class  DuaDhikrController extends Controller
{
    public function __construct(protected DuaDhikrService $duaDhikrService)
    {
        //
    }

    /**
     * Get duas by category ID.
     */
    public function getByCategory(Request $request, string $categoryId)
    {
        try {
            $result = $this->duaDhikrService->getByCategory($request, (int) $categoryId);

            return Helper::jsonResponse(true, 'Duas fetched successfully', 200, $result);
        } catch (Exception $e) {
            Log::error('DuaDhikrController::getByCategory' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get duas by category slug.
     */
    public function getByCategorySlug(Request $request, string $slug)
    {
        try {
            $result = $this->duaDhikrService->getByCategorySlug($request, $slug);

            return Helper::jsonResponse(true, 'Duas fetched successfully', 200, $result, true);
        } catch (Exception $e) {
            Log::error('DuaDhikrController::getByCategorySlug' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified dua.
     */
    public function show(string $id)
    {
        try {
            $dua = $this->duaDhikrService->show((int) $id);

            return Helper::jsonResponse(true, 'Dua fetched successfully', 200, $dua);
        } catch (Exception $e) {
            Log::error('DuaDhikrController::show' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Search duas.
     */
    public function search(Request $request)
    {
        try {
            $duas = $this->duaDhikrService->search($request);

            return Helper::jsonResponse(true, 'Duas fetched successfully', 200, $duas, true);
        } catch (Exception $e) {
            Log::error('DuaDhikrController::search' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
