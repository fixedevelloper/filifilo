<?php


namespace App\Http\Controllers\API\V2\Driver;

use App\Events\DriverLocationUpdated;
use App\Events\NearbyDriverUpdateEvent;
use App\Events\TransporterPositionUpdated;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DriverController extends Controller
{
    public function summary() {
        $user = Auth::user();

        if (!$user) {
            return Helpers::error('driver est requis', 400);
        }
        $data=[
            'total_order' => 0,
            'order_complet' => 0,
            'order_failed' => 0,
            'order_income' => 0.0,
           'rating'=>0,
            'points'=>0,
             'amount_collet'=>0.0,
            'payment_collet'=>0.0
        ];
        return Helpers::success($data, 'Profile récupérés avec succès');
    }
    public function profile() {
        $user = Auth::user();

        if (!$user) {
            return Helpers::error('driver est requis', 400);
        }
        return Helpers::success([
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'balance' => 0.0,
            'date_birth' => date('Y-m-d')
        ], 'Profile récupérés avec succès');
    }
    public function updateProfile(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
        ]);
        $customer = Auth::user();

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);


        return Helpers::success([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => 0.0,
            'date_birth' => date('Y-m-d')
        ]);
    }
    public function updatePosition(Request $request)
    {
        $driverId = $request->input('driver_id');
        $device = $request->input('device_id');
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
       // logger('------------------------'.$driverId);
        $driver = tap(
            Driver::where('device_id', $device)->firstOrFail()
        )->update([
            'current_latitude'  => $lat,
            'current_longitude' => $lng,
        ]);
        broadcast(new DriverLocationUpdated(
            $driverId,
            $lat,
            $lng
        ));

        $clients = cache()->get('active_clients', []);
        foreach ($clients as $userId => $pickup) {
            $distance = $this->haversineDistance(
                $pickup['lat'], $pickup['lng'],
                $driver->current_latitude, $driver->current_longitude
            );
            logger($distance);
            if ($distance <= 5) {
                logger('****proche*******');
                // Chauffeur proche → on push au client via Pusher
                broadcast(new NearbyDriverUpdateEvent([
                    'id'=>$driver->id,
                    'latitude'=>$driver->current_latitude,
                    'longitude'=>$driver->current_longitude,
                    'name'=>$driver->user->name
                ], $userId));
            }
        }
        $this->getLastCourseByDriver($driver->id, $lat, $lng);

        return response()->json(['status' => 'ok']);
    }
    private function getLastCourseByDriver($driverId,$lat,$lng){

        $deliveries=Delivery::query()->where(['driver_id'=>$driverId,'status'=>'in_delivery'])->latest()->get();
        foreach ($deliveries as $delivery){
            event(new TransporterPositionUpdated([ 'transporterId'=>$delivery->order_id,
                'lat'=>$lat,
                'lng'=>$lng]));
        }
    }
    private function getLastOneCourseByDriver($driverId, $lat, $lng)
    {
        $delivery = Delivery::where([
            'driver_id' => $driverId,
            'status'    => 'in_delivery'
        ])->latest()->first();

        if ($delivery) {
            logger("Delivery ID : {$delivery->id}");
            event(new TransporterPositionUpdated([
                'transporterId' => $delivery->order_id,
                'lat'           => $lat,
                'lng'           => $lng
            ]));
        }
    }

    public function registerClientPickup(Request $request)
    {
        $userId = $request->user()->id;
        $pickup = [
            'lat' => $request->latitude,
            'lng' => $request->longitude
        ];

        // Stocke en cache pour être vérifié à chaque update de chauffeur
        $clients = cache()->get('active_clients', []);
        $clients[$userId] = $pickup;
        cache()->put('active_clients', $clients, now()->addMinutes(10));

        return Helpers::success([]);
    }

    // 📏 Fonction pour calculer la distance entre 2 coordonnées
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Rayon de la Terre en km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}

