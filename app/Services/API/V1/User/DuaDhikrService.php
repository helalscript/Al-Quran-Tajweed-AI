<?php

namespace App\Services\API\V1\User;

use App\Models\Category;
use App\Models\DuaDhikr;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DuaDhikrService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all duas by category.
     *
     * @param mixed $request
     * @param int $categoryId
     * @return mixed
     */
    public function getByCategory($request, int $categoryId)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $languageCode = $request->language_code ?? $this->user->language_code ?? 'en';

            $category = Category::where('id', $categoryId)
                ->where('type', 'dua')
                ->where('status', 'active')
                ->first();

            if (!$category) {
                throw new Exception('Category not found');
            }

            $duas = DuaDhikr::where('category_id', $categoryId)
                ->where('language_code', $languageCode)
                ->where('status', 'active')
                ->orderBy('order', 'asc')
                ->paginate($perPage);

            // Add favourite status for each dua
            if ($this->user) {
                $duaIds = $duas->pluck('id')->toArray();
                $favouriteIds = $this->user->favourites()
                    ->where('favouritable_type', DuaDhikr::class)
                    ->whereIn('favouritable_id', $duaIds)
                    ->pluck('favouritable_id')
                    ->toArray();

                $duas->getCollection()->transform(function ($dua) use ($favouriteIds) {
                    $dua->is_favourite = in_array($dua->id, $favouriteIds);
                    return $dua;
                });
            }

            return [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'duas' => $duas,
            ];
        } catch (Exception $e) {
            Log::error("DuaDhikrService::getByCategory" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get duas by category slug.
     *
     * @param mixed $request
     * @param string $slug
     * @return mixed
     */
    public function getByCategorySlug($request, string $slug)
    {
        try {
            $category = Category::where('slug', $slug)
                ->where('type', 'dua')
                ->where('status', 'active')
                ->first();

            if (!$category) {
                throw new Exception('Category not found');
            }

            return $this->getByCategory($request, $category->id);
        } catch (Exception $e) {
            Log::error("DuaDhikrService::getByCategorySlug" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific dua.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $dua = DuaDhikr::where('id', $id)
                ->where('status', 'active')
                ->with('category')
                ->first();

            if (!$dua) {
                throw new Exception('Dua not found');
            }

            // Add favourite status
            if ($this->user) {
                $isFavourite = $this->user->favourites()
                    ->where('favouritable_type', DuaDhikr::class)
                    ->where('favouritable_id', $id)
                    ->exists();
                
                $dua->is_favourite = $isFavourite;
            }

            return $dua;
        } catch (Exception $e) {
            Log::error("DuaDhikrService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search duas.
     *
     * @param mixed $request
     * @return mixed
     */
    public function search($request)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $languageCode = $request->language_code ?? $this->user->language_code ?? 'en';
            $query = $request->query ?? '';

            $duas = DuaDhikr::where('status', 'active')
                ->where('language_code', $languageCode)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('arabic', 'like', "%{$query}%")
                        ->orWhere('latin', 'like', "%{$query}%")
                        ->orWhere('translation', 'like', "%{$query}%");
                })
                ->with('category')
                ->orderBy('order', 'asc')
                ->paginate($perPage);

            // Add favourite status
            if ($this->user) {
                $duaIds = $duas->pluck('id')->toArray();
                $favouriteIds = $this->user->favourites()
                    ->where('favouritable_type', DuaDhikr::class)
                    ->whereIn('favouritable_id', $duaIds)
                    ->pluck('favouritable_id')
                    ->toArray();

                $duas->getCollection()->transform(function ($dua) use ($favouriteIds) {
                    $dua->is_favourite = in_array($dua->id, $favouriteIds);
                    return $dua;
                });
            }

            return $duas;
        } catch (Exception $e) {
            Log::error("DuaDhikrService::search" . $e->getMessage());
            throw $e;
        }
    }
}

