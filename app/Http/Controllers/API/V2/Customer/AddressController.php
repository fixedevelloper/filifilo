<?php


namespace App\Http\Controllers\API\V2\Customer;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index() {
        $user = Auth::user();
        $notifications=Address::query()->where(['customer_id'=>$user->customer->id])->latest()->get();
        return Helpers::success($notifications);
    }
    public function store(Request $request) {
        $user = Auth::user();
        $validated = $request->validate([
            'label'     => 'required_if:user_type,customer|string|max:255',
            'addressLine'=> 'required_if:user_type,customer|string|max:255',
            'latitude'    => 'required_if:user_type,customer|numeric',
            'longitude'   => 'required_if:user_type,customer|numeric',
        ]);
        $address = Address::create([
            'label'        => $validated['label'],
            'address_line' => $validated['addressLine'],
            'latitude'     => $validated['latitude'],
            'longitude'    => $validated['longitude'],
            'customer_id'  => $user->customer->id,
            'city_id'=>1,
            'country_id'=>1,
        ]);
        return Helpers::success($address);
    }
    public function show($id) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
