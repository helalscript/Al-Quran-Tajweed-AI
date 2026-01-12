<?php

namespace App\Services\API\V1\User;

use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all categories.
     *
     * @param mixed $request
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $search = $request->search ?? '';
            $languageCode = $request->language_code ?? $this->user->language_code ?? 'en';

            $categories = Category::where('type', 'dua')
                ->select('id', 'name', 'slug', 'type', 'order')
                ->where('status', 'active')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                })
                ->withCount('duaDhikrs')
                ->orderBy('order', 'asc')
                ->paginate($perPage);

            // // Transform categories to include localized name
            // $categories->getCollection()->transform(function ($category) use ($languageCode) {
            //     $translations = $category->translations ?? [];
            //     $localizedCategories = $translations[$languageCode] ?? $translations['en'] ?? [];
                
            //     // Find matching category in translations
            //     $localizedCategory = collect($localizedCategories)->firstWhere('slug', $category->slug);
                
            //     return [
            //         'id' => $category->id,
            //         'name' => $localizedCategory['name'] ?? $category->name,
            //         'slug' => $category->slug,
            //         'type' => $category->type,
            //         'order' => $category->order,
            //         'duas_count' => $category->duaDhikrs()->where('status', 'active')->count(),
            //     ];
            // });

            return $categories;
        } catch (Exception $e) {
            Log::error("CategoryService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific category.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $category = Category::where('id', $id)
                ->select('id', 'name', 'slug', 'type', 'order')
                ->where('type', 'dua')
                ->where('status', 'active')
                ->withCount('duaDhikrs')
                ->with(['duaDhikrs'=>function($query) {
                    $query->where('status', 'active')
                    ->select('id','category_id', 'title','order');
                }])
                ->first();

            if (!$category) {
                throw new Exception('Category not found');
            }

            return $category;
        } catch (Exception $e) {
            Log::error("CategoryService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get category by slug.
     *
     * @param string $slug
     * @return mixed
     */
    public function getBySlug(string $slug)
    {
        try {
            $category = Category::where('slug', $slug)
                ->where('type', 'dua')
                ->where('status', 'active')
                ->withCount('duaDhikrs')
                ->with('duaDhikrs')
                ->first();

            if (!$category) {
                throw new Exception('Category not found');
            }

            return $category;
        } catch (Exception $e) {
            Log::error("CategoryService::getBySlug" . $e->getMessage());
            throw $e;
        }
    }
}

