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
                ->withCount('duaDhikirs')
                ->orderBy('order', 'asc')
                ->paginate($perPage);

            // Transform categories to include localized name if needed, and fix counts
            $categories->getCollection()->transform(function ($category) use ($languageCode) {
                return [
                    'id' => $category->id,
                    'name' => $category->name, // Categories themselves might need translations later, but for now they have a name
                    'slug' => $category->slug,
                    'type' => $category->type,
                    'order' => $category->order,
                    'duas_count' => $category->dua_dhikirs_count,
                ];
            });

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
            $languageCode = request()->language_code ?? $this->user->language_code ?? 'en';

            $category = Category::where('id', $id)
                ->select('id', 'name', 'slug', 'type', 'order')
                ->where('type', 'dua')
                ->where('status', 'active')
                ->withCount(['duaDhikirs' => function($query) {
                    $query->where('status', 'active');
                }])
                ->with(['duaDhikirs' => function($query) use ($languageCode) {
                    $query->where('status', 'active')
                        ->select('id', 'category_id', 'order')
                        ->with(['translations' => function($q) use ($languageCode) {
                            $q->where('language_code', $languageCode)
                              ->select('id', 'dua_dhikir_id', 'title');
                        }]);
                }])
                ->first();

            if ($category) {
                // Flatten translations for easier API consumption
                $category->duaDhikirs->transform(function($dua) {
                    $translation = $dua->translations->first();
                    $dua->title = $translation ? $translation->title : null;
                    unset($dua->translations);
                    return $dua;
                });
            }

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
            $languageCode = request()->language_code ?? $this->user->language_code ?? 'en';

            $category = Category::where('slug', $slug)
                ->where('type', 'dua')
                ->where('status', 'active')
                ->withCount(['duaDhikirs' => function($query) {
                    $query->where('status', 'active');
                }])
                ->with(['duaDhikirs' => function($query) use ($languageCode) {
                    $query->where('status', 'active')
                        ->with(['translations' => function($q) use ($languageCode) {
                            $q->where('language_code', $languageCode);
                        }]);
                }])
                ->first();

            if ($category) {
                // Flatten translations for easier API consumption
                $category->duaDhikirs->transform(function($dua) {
                    $translation = $dua->translations->first();
                    if ($translation) {
                        $dua->title = $translation->title;
                        $dua->translation = $translation->translation;
                        $dua->notes = $translation->notes;
                        $dua->benefits = $translation->benefits;
                        $dua->fawaid = $translation->fawaid;
                    }
                    unset($dua->translations);
                    return $dua;
                });
            }

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

