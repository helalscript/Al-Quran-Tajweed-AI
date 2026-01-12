<?php

namespace App\Services\API\V1\User;

use App\Models\Category;
use App\Models\DuaDhikr;
use App\Models\Favourite;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavouriteService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all favourites.
     *
     * @param  mixed  $request
     * @return mixed
     */
    public function index($request)
    {
        try {
            if (! $this->user) {
                throw new Exception('User not authenticated');
            }

            $perPage = $request->per_page ?? 25;
            $type = $request->type ?? null; // 'category' or 'dua_dhikr'

            $query = $this->user->favourites()->with('favouritable');

            if ($type === 'category') {
                $query->where('favouritable_type', Category::class);
            } elseif ($type === 'dua_dhikr') {
                $query->where('favouritable_type', DuaDhikr::class);
            }

            $favourites = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Transform favourites to include favouritable data
            $favourites->getCollection()->transform(function ($favourite) {
                $favouritable = $favourite->favouritable;

                if ($favouritable instanceof DuaDhikr) {
                    return [
                        'id' => $favourite->id,
                        'type' => 'dua_dhikr',
                        'dua' => [
                            'id' => $favouritable->id,
                            'title' => $favouritable->title,
                            'arabic' => $favouritable->arabic,
                            'latin' => $favouritable->latin,
                            'translation' => $favouritable->translation,
                            'category' => [
                                'id' => $favouritable->category->id,
                                'name' => $favouritable->category->name,
                                'slug' => $favouritable->category->slug,
                            ],
                        ],
                        'created_at' => $favourite->created_at,
                    ];
                } elseif ($favouritable instanceof Category) {
                    return [
                        'id' => $favourite->id,
                        'type' => 'category',
                        'category' => [
                            'id' => $favouritable->id,
                            'name' => $favouritable->name,
                            'slug' => $favouritable->slug,
                            'duas_count' => $favouritable->duaDhikrs()->where('status', 'active')->count(),
                        ],
                        'created_at' => $favourite->created_at,
                    ];
                }

                return $favourite;
            });

            return $favourites;
        } catch (Exception $e) {
            Log::error('FavouriteService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Add a favourite.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            if (! $this->user) {
                throw new Exception('User not authenticated');
            }

            $favouritableType = $validatedData['favouritable_type'] === 'category' ? Category::class : DuaDhikr::class;
            $favouritableId = $validatedData['favouritable_id'];

            // Validate favouritable type
            $allowedTypes = [Category::class, DuaDhikr::class];
            if (! in_array($favouritableType, $allowedTypes)) {
                throw new Exception('Invalid favouritable type');
            }

            // Check if favouritable exists
            $favouritable = $favouritableType::find($favouritableId);
            if (! $favouritable) {
                throw new Exception('Favouritable item not found');
            }

            // Check if already favourited
            $existing = Favourite::where('user_id', $this->user->id)
                ->where('favouritable_type', $favouritableType)
                ->where('favouritable_id', $favouritableId)
                ->first();

            if ($existing) {
                throw new Exception('Already added to favourites');
            }

            $favourite = Favourite::create([
                'user_id' => $this->user->id,
                'favouritable_type' => $favouritableType,
                'favouritable_id' => $favouritableId,
            ]);

            return $favourite->load('favouritable');
        } catch (Exception $e) {
            Log::error('FavouriteService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove a favourite.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            if (! $this->user) {
                throw new Exception('User not authenticated');
            }

            $favourite = Favourite::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (! $favourite) {
                throw new Exception('Favourite not found');
            }

            $favourite->delete();

            return true;
        } catch (Exception $e) {
            Log::error('FavouriteService::destroy'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Toggle favourite status.
     *
     * @return mixed
     */
    public function toggle(array $validatedData)
    {
        try {
            if (! $this->user) {
                throw new Exception('User not authenticated');
            }

            $favouritableType = $validatedData['favouritable_type'] === 'category' ? Category::class : DuaDhikr::class;
            $favouritableId = $validatedData['favouritable_id'];

            $favourite = Favourite::where('user_id', $this->user->id)
                ->where('favouritable_type', $favouritableType)
                ->where('favouritable_id', $favouritableId)
                ->first();

            if ($favourite) {
                $favourite->delete();

                return ['is_favourite' => false];
            } else {
                $favourite = Favourite::create([
                    'user_id' => $this->user->id,
                    'favouritable_type' => $favouritableType,
                    'favouritable_id' => $favouritableId,
                ]);

                return ['is_favourite' => true, 'favourite' => $favourite];
            }
        } catch (Exception $e) {
            Log::error('FavouriteService::toggle'.$e->getMessage());
            throw $e;
        }
    }
}
