<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DistanceService
{
    protected $googleMapsApiKey;
    protected $restaurantLat;
    protected $restaurantLng;

    public function __construct()
    {
        $this->googleMapsApiKey = config('services.google_maps.api_key');
        $this->restaurantLat = config('services.google_maps.restaurant_lat') ?: 21.107140786131264;
        $this->restaurantLng = config('services.google_maps.restaurant_lng') ?: 105.55805747568722;
    }

    /**
     * Convert address to coordinates using Nominatim (OpenStreetMap) - miễn phí không cần API key
     */
    public function geocodeAddress($address)
    {
        try {
            // Tạo danh sách các địa chỉ thử theo thứ tự ưu tiên (từ chi tiết đến đơn giản)
            $addressesToTry = [$address];
            
            // Tách địa chỉ bằng dấu phẩy và thử bớt dần phần đầu
            $parts = explode(',', $address);
            for ($i = 1; $i < count($parts); $i++) {
                $addressesToTry[] = trim(implode(',', array_slice($parts, $i)));
            }

            foreach ($addressesToTry as $tryAddress) {
                if (empty($tryAddress)) continue;

                $response = Http::timeout(3) // Timeout 3s tối đa tránh treo trang web
                    ->withHeaders(['User-Agent' => 'CIVILIZE-Restaurant-Delivery/1.0 (contact@civilize.vn)'])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $tryAddress,
                        'format' => 'json',
                        'limit' => 1,
                        'countrycodes' => 'vn',
                        'accept-language' => 'vi-VN'
                    ]);

                if (!$response->failed() && !empty($response->json())) {
                    $result = $response->json()[0];
                    return [
                        'lat' => (float)$result['lat'],
                        'lng' => (float)$result['lon']
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Geocode Nominatim exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Calculate distance between two points using Haversine formula (in kilometers)
     */
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if customer address is within allowed distance
     */
    public function isWithinDeliveryRange($address, $maxDistance = 5)
    {
        try {
            $coordinates = $this->geocodeAddress($address);

            // Nếu không xác định được tọa độ chính xác (ví dụ địa chỉ thôn xóm, ngõ ngách),
            // vẫn chấp nhận đơn hàng để nhân viên liên hệ xác nhận trực tiếp, không chặn khách đặt món.
            if (!$coordinates) {
                return [
                    'success' => true,
                    'message' => 'Địa chỉ giao hàng đã được ghi nhận. Nhà hàng sẽ liên hệ xác nhận lộ trình giao nhanh.',
                    'distance' => null,
                ];
            }

            // Tính khoảng cách đường đi thực tế dùng OSRM
            $distance = $this->getDrivingDistance(
                $this->restaurantLat,
                $this->restaurantLng,
                $coordinates['lat'],
                $coordinates['lng']
            );

            // Nếu OSRM không hoạt động, fallback về khoảng cách đường chim bay
            if ($distance === null) {
                $distance = $this->calculateDistance(
                    $this->restaurantLat,
                    $this->restaurantLng,
                    $coordinates['lat'],
                    $coordinates['lng']
                );
            }

            // Cho phép đặt hàng và thông báo trạng thái khoảng cách
            return [
                'success' => true,
                'message' => ($distance <= $maxDistance) 
                    ? 'Địa chỉ trong phạm vi giao hàng tiêu chuẩn.' 
                    : 'Địa chỉ xa hơn 5km, nhà hàng sẽ liên hệ thông báo phí ship ưu đãi.',
                'distance' => round($distance, 1),
            ];
        } catch (\Exception $e) {
            Log::warning('isWithinDeliveryRange exception: ' . $e->getMessage());
            return [
                'success' => true,
                'message' => 'Địa chỉ đã được ghi nhận thành công.',
                'distance' => null,
            ];
        }
    }

    /**
     * Tính khoảng cách đường đi thực tế dùng OSRM (Open Source Routing Machine)
     */
    private function getDrivingDistance($lat1, $lng1, $lat2, $lng2)
    {
        $url = "https://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";

        try {
            $response = Http::timeout(3)->get($url);

            if ($response->successful() && isset($response->json()['routes'][0]['distance'])) {
                // Khoảng cách trả về bằng mét, đổi sang km
                return $response->json()['routes'][0]['distance'] / 1000;
            }
        } catch (\Exception $e) {
            Log::warning('OSRM error: ' . $e->getMessage());
        }

        return null;
    }
}
