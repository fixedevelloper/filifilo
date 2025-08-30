<?php


namespace App\Http\Controllers\API\V2\Merchant;


use App\Events\NewNotification;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
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
    public function accept(Request $request) {
        $validator=   Validator::make($request->all(),[
            'status' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            $err = null;
            foreach ($validator->errors()->all() as $error) {
                $err = $error;
            }
            return Helpers::error($err);
        }
        $order=Order::query()->find($request->order_id);
        $order->update([
            'status'=>$request->status
        ]);
        if ($request->status == Order::IN_DELIVERY) {

            $drivers = Driver::query()
                ->whereDoesntHave('deliveries', function ($query) {
                    $query->where(['status'=>'assigned']);
                })
                ->get();

            foreach ($drivers as $driver) {


                $notification=Notification::create([
                    'order_id'=>$order->id,
                    'recipient_id' => $driver->id,
                    "recipient_type" => 'driver',
                    "message" => "Commande en preparation au magasin {$order->store->addresse}",
                    'title'=>'Commande en preparation',
                ]);


                // Envoi en temps réel
         /*       broadcast(new NewNotification([
                    'user_id'       => $driver->id,
                    'username'      => $driver->first_name, // ✅ cohérent
                    'profile_image' => $driver->profile_image,
                    'action_text'   => "Placed a new order",
                    'time'          => $notification->time_ago, // Accessoire du modèle
                    'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
                ]));*/
            }
            $notification_admin=Notification::create([
                'order_id'=>$order->id,
                'recipient_id' => 1,
                "recipient_type" => 'admin',
                "message" => "Commande en preparation",
                'title'=>'Commande en preparation',
            ]);
        }
            $notification_admin=Notification::create([
                'order_id'=>$order->id,
                'recipient_id' => 1,
                "recipient_type" => 'admin',
                "message" => "Commande a changer de status {$order->status}",
                'title'=>"Commande en {$order->status}",
            ]);
        $notification_customer=Notification::create([
            'order_id'=>$order->id,
            'recipient_id' => $order->customer_id,
            "recipient_type" => 'admin',
            "message" => "Commande a changer de status {$order->status}",
            'title'=>"Commande en {$order->status}",
        ]);

        return Helpers::success($order, 'Produit créée avec succès');
    }
    public function reject($id) {}
    public function updatePreparationTime(Request $request, $id) {}
}
