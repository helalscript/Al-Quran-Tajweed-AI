<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\DuaDhikirService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class  DuaDhikirController extends Controller
{
    public function __construct(protected DuaDhikirService $duaDhikirService)
    {
        //
    }

    /**
     * Get duas by category ID.
     */
    public function getByCategory(Request $request, string $categoryId)
    {
        try {
            $result = $this->duaDhikirService->getByCategory($request, (int) $categoryId);

            return Helper::jsonResponse(true, 'Duas fetched successfully', 200, $result);
        } catch (Exception $e) {
            Log::error('DuaDhikirController::getByCategory' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get duas by category slug.
     */
    public function getByCategorySlug(Request $request, string $slug)
    {
        try {
            $result = $this->duaDhikirService->getByCategorySlug($request, $slug);

            return Helper::jsonResponse(true, 'Duas fetched successfully', 200, $result, true);
        } catch (Exception $e) {
            Log::error('DuaDhikirController::getByCategorySlug' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified dua.
     */
    public function show(string $id)
    {
        try {
            $dua = $this->duaDhikirService->show((int) $id);

            return Helper::jsonResponse(true, 'Dua fetched successfully', 200, $dua);
        } catch (Exception $e) {
            Log::error('DuaDhikirController::show' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Search duas.
     */
    public function search(Request $request)
    {
        try {
            $duas = $this->duaDhikirService->search($request);

            return Helper::jsonResponse(true, 'Duas fetched successfully', 200, $duas, true);
        } catch (Exception $e) {
            Log::error('DuaDhikirController::search' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
