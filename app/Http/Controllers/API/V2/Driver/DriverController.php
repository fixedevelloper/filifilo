<?php


namespace App\Http\Controllers\API\V2\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function profile() {}
    public function updateProfile(Request $request) {}
    public function updatePosition(Request $request)
    {
        $driverId = $request->input('driver_id');
        $device = $request->input('device_id');
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $pos = Driver::where(['device_id'=>$device])->update([
            'current_latitude'  => $lat,
            'current_longitude' => $lng,
        ]);

        logger($device); // va logguer 1 si update ok, 0 sinon

        return response()->json(['status' => 'ok']);
    }

}
