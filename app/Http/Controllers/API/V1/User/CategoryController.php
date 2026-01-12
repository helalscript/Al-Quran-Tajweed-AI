<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService)
    {
        //
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        try {
            $categories = $this->categoryService->index($request);

            return Helper::jsonResponse(true, 'Categories fetched successfully', 200, $categories, true);
        } catch (Exception $e) {
            Log::error('CategoryController::index' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(string $id)
    {
        try {
            $category = $this->categoryService->show((int) $id);

            return Helper::jsonResponse(true, 'Category fetched successfully', 200, $category);
        } catch (Exception $e) {
            Log::error('CategoryController::show' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get category by slug.
     */
    public function getBySlug(Request $request, string $slug)
    {
        try {
            $category = $this->categoryService->getBySlug($slug);

            return Helper::jsonResponse(true, 'Category fetched successfully', 200, $category);
        } catch (Exception $e) {
            Log::error('CategoryController::getBySlug' . $e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
