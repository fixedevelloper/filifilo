<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeoHelper
{
    public static function getAddressFromCoordinates($latitude, $longitude)
    {
        // Clé unique pour le cache basée sur les coordonnées
        $cacheKey = "geo_address_{$latitude}_{$longitude}";

        // Tenter de récupérer depuis le cache
        return Cache::remember($cacheKey, 60 * 24, function () use ($latitude, $longitude) {
            $url = "https://nominatim.openstreetmap.org/reverse";

            $response = Http::get($url, [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['address'])) {
                    $address = $data['address'];

                    $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? null;
                    $country = $address['country'] ?? null;

                    return [
                        'city' => $city,
                        'country' => $country
                    ];
                }
            }

            return null;
        });
    }
}
