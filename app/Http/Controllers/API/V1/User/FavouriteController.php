<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DuaDhikr;
use App\Services\API\V1\User\FavouriteService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    public function __construct(protected FavouriteService $favouriteService)
    {
        //
    }

    /**
     * Display a listing of favourites.
     */
    public function index(Request $request)
    {
        try {
            $favourites = $this->favouriteService->index($request);

            return Helper::jsonResponse(true, 'Favourites fetched successfully', 200, $favourites, true);
        } catch (Exception $e) {
            Log::error('FavouriteController::index' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created favourite.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'favouritable_type' => 'required|string|in:category,dua_dhikr',
                'favouritable_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return Helper::jsonErrorResponse($validator->errors()->first(), 422);
            }

            $favourite = $this->favouriteService->store($validator->validated());

            return Helper::jsonResponse(true, 'Added to favourites successfully', 201, $favourite);
        } catch (Exception $e) {
            Log::error('FavouriteController::store' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified favourite.
     */
    public function destroy(string $id)
    {
        try {
            $this->favouriteService->destroy((int) $id);

            return Helper::jsonResponse(true, 'Removed from favourites successfully', 200);
        } catch (Exception $e) {
            Log::error('FavouriteController::destroy' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Toggle favourite status.
     */
    public function toggle(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'favouritable_type' => 'required|string|in:category,dua_dhikr',
                'favouritable_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return Helper::jsonErrorResponse($validator->errors()->first(), 422);
            }

            $result = $this->favouriteService->toggle($validator->validated());

            return Helper::jsonResponse(true, 'Favourite status updated successfully', 200, $result);
        } catch (Exception $e) {
            Log::error('FavouriteController::toggle' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
