<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class QiblaDirectionService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }
    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function getDirection($request)
    {
        try {
            $latitude = $request['latitude'];
            $longitude = $request['longitude'];

            //    https://api.aladhan.com/v1/qibla/23.8103/90.4125
            $apiUrl = "https://api.aladhan.com/v1/qibla/{$latitude}/{$longitude}";

            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $apiUrl);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                return $data['data'];
            } else {
                throw new Exception("Failed to fetch Qibla direction from external API.");
            }

        } catch (Exception $e) {
            Log::error("QiblaDirectionService::index" . $e->getMessage());
            throw $e;

        }
    }



}