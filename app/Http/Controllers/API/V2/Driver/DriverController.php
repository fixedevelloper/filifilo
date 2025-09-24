<?php


namespace App\Http\Controllers\API\V2\Driver;

use App\Events\TransporterPositionUpdated;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        logger('------------------------'.$driverId);
        $driver = tap(
            Driver::where('device_id', $device)->firstOrFail()
        )->update([
            'current_latitude'  => $lat,
            'current_longitude' => $lng,
        ]);

        $this->getLastCourseByDriver($driver->id, $lat, $lng);


        return response()->json(['status' => 'ok']);
    }
    private function getLastCourseByDriver($driverId,$lat,$lng){
        $deliveries=Delivery::query()->where(['driver_id'=>$driverId,'status'=>'in_delivery'])->latest()->get();
        logger('------------------------'.$deliveries);
        foreach ($deliveries as $delivery){

            logger('tttttttttttttt'.$delivery->id);
            event(new TransporterPositionUpdated([ 'transporterId'=>$delivery->order_id,
                'lat'=>$lat,
                'lng'=>$lng]));
        }
    }
}
