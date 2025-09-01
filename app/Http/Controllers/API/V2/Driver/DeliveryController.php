<?php


namespace App\Http\Controllers\API\V2\Driver;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function Carbon\ne;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Delivery::query();

        if ($request->status == 'current') {
            $query->where('status', 'current');
        } elseif ($request->status == 'accepted') {
            $query->where('driver_id', $user->driver->id)
                ->where(function ($q) {
                    $q->where('status', 'assigned')
                        ->orWhere('status', 'in_delivered');
                });
        } else {
            $query->where('driver_id', $user->driver->id)
                ->where('status', 'delivered');
        }

        $deliveries = $query->get();

        return Helpers::success(DeliveryResource::collection($deliveries));
    }

    public function accept($id)
    {
        $user = Auth::user();
        $delivery = Delivery::findOrFail($id);

        if (!is_null($delivery->driver_id)) {
            return Helpers::unauthorized([], 'Commande déjà attribuée');
        }

        $delivery->update([
            'status' => 'assigned',
            'driver_id' => $user->driver->id,
        ]);

        return Helpers::success(new DeliveryResource($delivery));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:assigned,in_delivered,delivered',
        ]);

        $delivery = Delivery::where('driver_id', $user->driver->id)->findOrFail($id);

        $delivery->update([
            'status' => $request->status,
        ]);

        return Helpers::success(new DeliveryResource($delivery));
    }
    public function show($id) {
        $order=Delivery::find($id);
        return Helpers::success(new DeliveryResource($order));
    }
}
