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

        // Validation : s'assurer que le status est correct
        $request->validate([
            'status' => 'required|in:assigned,in_delivery,delivered', // Le statut doit correspondre aux valeurs possibles
        ]);

        // Chercher la livraison et vérifier les droits du driver
        $delivery = Delivery::where('id', $id)
            ->where(function($query) use ($user) {
                // Si le statut est 'assigned', n'importe quel utilisateur avec un driver_id valide peut l'assigner
                // Sinon, on vérifie que le driver associé à la livraison est bien celui de l'utilisateur connecté
                if ($user->driver_id) {
                    $query->where('driver_id', $user->driver_id);
                }
            })
            ->first();

        // Si la livraison n'est pas trouvée ou l'utilisateur n'a pas les droits, retourner une erreur
        if (!$delivery) {
            return response()->json(['error' => 'You do not have permission or this delivery does not exist.'], 403);
        }

        // Mise à jour du statut
        if ($request->status == 'assigned') {
            $delivery->update([
                'status' => 'assigned',
                'driver_id' => $user->driver->id,
            ]);
        } else {
            $delivery->update([
                'status' => $request->status,   // Mettre à jour seulement le statut
            ]);
        }

        // Retourner la réponse de succès
        return Helpers::success(new DeliveryResource($delivery));
    }

    public function show($id) {
        $order=Delivery::find($id);
        return Helpers::success(new DeliveryResource($order));
    }
}
