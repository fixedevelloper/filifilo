<?php


namespace App\Http\Controllers\API\V2\Merchant;


use App\Events\NewNotification;
use App\Helpers\api\Helpers;
use App\Helpers\FCMService;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index($id) {
        $user = Auth::user();

        $orders = Order::where('store_id', $id)->orderByDesc("created_at")->get()->map(function ($order)
        {
            $lines=OrderItem::where('order_id',$order->id)->get()->map(function ($line){
                return [
                    'id'          => $line->id,
                    'name'          => $line->product_name,
                    'quantity'          => $line->quantity,
                    'price'          => $line->unit_price,
                    'total_price'          => $line->total_price,
                ];
            });
            return [
                'id'          => $order->id,
                'reference'        => $order->reference,
                'quantity'       => $order->quantity,
                'status'   => $order->status,
                'total_ttc' => $order->total_amount,
                'total' => $order->total_amount,
                'items'=>$lines,
                'store_name' => $order->store->name,
                'date' => $order->created_at->toDateTimeString(),
            ];
        });

        return Helpers::success($orders, 'Commandes récupérés avec succès');
    }
    public function show($id) {
        $order = Order::with(['store', 'orderItems','customer','deliveryAddress','latestDelivery'])->find($id);

        if (!$order) {
            return Helpers::error('Commande non trouvée');
        }

        logger($order);
        return Helpers::success(new OrderResource($order), 'Commande récupérée avec succès');
    }
    public function accept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return Helpers::error($validator->errors()->first());
        }

        $order = Order::findOrFail($request->order_id);
        $order->update(['status' => $request->status]);

        // Notifications for IN_DELIVERY status
        if ($request->status === Order::IN_DELIVERY) {
            Delivery::create(['order_id' => $order->id]);

            $drivers = Driver::whereDoesntHave('deliveries', function ($query) {
                $query->where('status', 'assigned');
            })->get();

            foreach ($drivers as $driver) {
                Notification::create([
                    'order_id'       => $order->id,
                    'recipient_id'   => $driver->id,
                    'recipient_type' => 'driver',
                    'title'          => 'Commande en préparation',
                    'message'        => "Commande en préparation au magasin {$order->store->addresse}",
                ]);

                FCMService::sendKer(
                    $driver->user->fcm_token,
                    'Nouvelle course',
                    "Une nouvelle course a été ajoutée au magasin {$order->store->addresse}"
                );
            }

            Notification::create([
                'order_id'       => $order->id,
                'recipient_id'   => 1,
                'recipient_type' => 'admin',
                'title'          => 'Commande en préparation',
                'message'        => 'Commande en préparation',
            ]);
        }

        // Notify Admin
        Notification::create([
            'order_id'       => $order->id,
            'recipient_id'   => 1,
            'recipient_type' => 'admin',
            'title'          => "Commande en {$order->status}",
            'message'        => "Commande a changé de statut : {$order->status}",
        ]);

        // Notify Customer
        Notification::create([
            'order_id'       => $order->id,
            'recipient_id'   => $order->customer_id,
            'recipient_type' => 'admin', // Should probably be 'customer'?
            'title'          => "Commande en {$order->status}",
            'message'        => "Commande a changé de statut : {$order->status}",
        ]);

        FCMService::sendKer(
            $order->customer->user->fcm_token,
            'Statut de commande',
            "Votre commande a changé de statut : {$order->status}"
        );

        return Helpers::success(new OrderResource($order), 'Commande mise à jour avec succès');
    }

    public function reject($id) {
        $order=Order::query()->find($id);
        $order->update([
            'status'=>Order::CANCELLED
        ]);
        FCMService::sendKer($order->customer->user->fcm_token,'Commande Rejete','Votre Commande a ete rejete');

        return Helpers::success(new OrderResource($order), 'Produit créée avec succès');
    }
    public function updatePreparationTime(Request $request, $id) {
        $order=Order::query()->find($request->order_id);
        $order->update([
            'status'=>$request->status
        ]);
    }
}
