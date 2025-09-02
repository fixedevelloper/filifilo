<?php


namespace App\Http\Controllers\API\V2\Driver;


use App\Events\NewNotification;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\Notification;
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

        // Validation : s'assurer que le statut est correct
        $request->validate([
            'status' => 'required|in:assigned,in_delivery,delivered',
        ]);

        // Chercher la livraison et vérifier les droits du driver
        $delivery = $this->findDeliveryForUser($id, $user);

        if (!$delivery) {
            return response()->json(['error' => 'Vous n\'avez pas la permission ou cette livraison n\'existe pas.'], 403);
        }

        // Traiter le changement de statut
        $status = $request->status;
        $this->handleStatusChange($delivery, $status, $user);

        // Retourner une réponse de succès
        return Helpers::success(new DeliveryResource($delivery));
    }

    /**
     * Trouver la livraison en fonction de l'utilisateur et de l'ID de la livraison.
     */
    private function findDeliveryForUser($id, $user)
    {
        return Delivery::where('id', $id)
            ->where(function ($query) use ($user) {
                if ($user->driver_id) {
                    $query->where('driver_id', $user->driver_id);
                }
            })
            ->first();
    }

    /**
     * Traiter le changement de statut.
     */
    private function handleStatusChange(Delivery $delivery, $status, $user)
    {
        switch ($status) {
            case 'assigned':
                $this->assignDelivery($delivery, $user);
                break;
            case 'in_delivery':
                $this->updateDeliveryStatus($delivery, 'in_delivery');
                $this->sendNotification($delivery, "La commande N° {$delivery->order->id} est en cours de livraison", 'Commande en cours de livraison');
                break;
            case 'delivered':
                $this->updateDeliveryStatus($delivery, 'delivered');
                $this->sendNotification($delivery, "La commande N° {$delivery->order->id} a été livrée avec succès", 'Commande livrée avec succès');
                break;
        }
    }

    /**
     * Mettre à jour le statut de la livraison.
     */
    private function updateDeliveryStatus(Delivery $delivery, $status)
    {
        $delivery->update(['status' => $status]);
        if (in_array($status,['in_delivery','delivered'])){
            $delivery->order()->update(['status' => $status]);
        }

    }

    /**
     * Assigner la livraison à un chauffeur.
     */
    private function assignDelivery(Delivery $delivery, $user)
    {
        $delivery->update([
            'status' => 'assigned',
            'driver_id' => $user->driver->id,
        ]);

        // Envoi des notifications pour l'assignation
        $this->sendNotification($delivery, "La commande N° {$delivery->order->id} a été assignée avec succès au transporteur {$delivery->driver->user->name}", 'Assignation de commande');
    }

    /**
     * Envoyer les notifications au marchand, au client et à l\'admin.
     */
    private function sendNotification(Delivery $delivery, $message, $title)
    {
        $merchantNotification = $this->createNotification($delivery, $delivery->order->store->merchant_id, 'merchant', $message, $title);
        $customerNotification = $this->createNotification($delivery, $delivery->order->customer->id, 'customer', $message, $title);
        $adminNotification = $this->createNotification($delivery, 1, 'merchant', $message, $title);

        // Diffusion des notifications
        broadcast(new NewNotification($merchantNotification));
        broadcast(new NewNotification($customerNotification));
        broadcast(new NewNotification($adminNotification));
    }

    /**
     * Créer une nouvelle notification.
     */
    private function createNotification(Delivery $delivery, $recipientId, $recipientType, $message, $title)
    {
        return Notification::create([
            'order_id' => $delivery->order->id,
            'recipient_id' => $recipientId,
            'recipient_type' => $recipientType,
            'message' => $message,
            'title' => $title,
        ]);
    }


    public function show($id)
    {
        $order = Delivery::find($id);
        return Helpers::success(new DeliveryResource($order));
    }
}
