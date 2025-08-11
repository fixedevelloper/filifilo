<?php


namespace App\Http\Controllers\API;


use App\Events\NewNotification;
use App\Events\TransporterPositionUpdated;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\LineItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Store;
use App\Models\TransporterPosition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransporterController extends Controller
{

    public function orders(Request $request)
    {
        $customer = Auth::user();

        $orders = Order::where('status', Order::EN_LIVRAISON)
            ->orderByDesc("created_at")
            ->get()
            ->map(function ($order) {
                $lines = LineItem::where('order_id', $order->id)->get()->map(function ($line) {
                    return [
                        'id'       => $line->id ?? 0,
                        'name'     => $line->name ?? '',
                        'quantity' => $line->quantity ?? 0,
                        'price'    => $line->price ?? 0.0,
                    ];
                });

                $customer = $order->customer;
                $store = $order->store;

                return [
                    'id'               => $order->id ?? 0,
                    'reference'        => $order->reference ?? '',
                    'quantity'         => $order->quantity ?? 0,
                    'status'           => $order->status ?? '',
                    'total_ttc'        => $order->total_ttc ?? 0.0,
                    'total'            => $order->total ?? 0.0,
                    'items'            => $lines,
                    'customer_name'    => ($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''),
                    'customer_address' => $customer->address ?? '',
                    'customer_phone'   => $customer->phone ?? '',
                    'store_name'       => $store->name ?? '',
                    'date'             => $order->created_at ? $order->created_at->toDateTimeString() : '',
                ];
            });

        return Helpers::success($orders, 'Commandes récupérées avec succès');
    }
    public function myOrders(Request $request)
    {
        $customer = Auth::user();

        $orders = Order::where('transporter_id', $customer->id)
            ->orderByDesc("created_at")
            ->get()
            ->map(function ($order) {
                $lines = LineItem::where('order_id', $order->id)->get()->map(function ($line) {
                    return [
                        'id'       => $line->id ?? 0,
                        'name'     => $line->name ?? '',
                        'quantity' => $line->quantity ?? 0,
                        'price'    => $line->price ?? 0.0,
                    ];
                });

                $customer = $order->customer;
                $store = $order->store;

                return [
                    'id'               => $order->id ?? 0,
                    'reference'        => $order->reference ?? '',
                    'quantity'         => $order->quantity ?? 0,
                    'status'           => $order->status ?? '',
                    'total_ttc'        => $order->total_ttc ?? 0.0,
                    'total'            => $order->total ?? 0.0,
                    'items'            => $lines,
                    'customer_name'    => ($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''),
                    'customer_address' => $customer->address ?? '',
                    'customer_phone'   => $customer->phone ?? '',
                    'store_name'       => $store->name ?? '',
                    'date'             => $order->created_at ? $order->created_at->toDateTimeString() : '',
                ];
            });

        return Helpers::success($orders, 'Commandes récupérées avec succès');
    }

    public function updateTransporterPosition(Request $request, $orderId)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // Stocker en base
        $pos = TransporterPosition::updateOrCreate(
            ['order_id' => $orderId],
            ['lat' => $lat, 'lng' => $lng]
        );

        // Diffuser événement WebSocket
        event(new TransporterPositionUpdated([ 'transporterId'=>7,
            'lat'=>$lat,
            'lng'=>$lng]));

        return response()->json(['status' => 'ok']);
    }
/*    public function updateTransporterPosition(Request $request, $orderId)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // Stocker en base
        $pos = TransporterPosition::updateOrCreate(
            ['order_id' => $orderId],
            ['lat' => $lat, 'lng' => $lng]
        );

        // Diffuser événement WebSocket
        event(new TransporterPositionUpdated([ 'transporterId'=>7,
            'lat'=>$lat,
            'lng'=>$lng]));

        return response()->json(['status' => 'ok']);
    }*/

    public function getTransporterPosition($orderId)
    {
        $pos = TransporterPosition::where('order_id', $orderId)->latest()->first();

        if ($pos) {
            return response()->json(['lat' => $pos->lat, 'lng' => $pos->lng]);
        }

        return response()->json(null, 404);
    }
    public function updateStatus(Request $request)
    {
        $manager = Auth::user();

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
        // Création en BDD
        $notification = Notification::create([
            'user_id'       => $order->customer->id,
            'username'      => $order->customer->first_name,
            'profile_image' => $order->customer->profile_image, // ✅ Image du client
            'action_text'   => "Votre commande est en cours de livraison",
            'time'          => now()->diffForHumans(), // ou via accessoire
            'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
        ]);

        // Envoi en temps réel
        broadcast(new NewNotification([
            'user_id'       => $order->customer->id,
            'username'      => $order->customer->first_name,
            'profile_image' => $order->customer->profile_image, // ✅ Image du client
            'action_text'   => "Votre commande est en cours de livraison",
            'time'          => now()->diffForHumans(), // ou via accessoire
            'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
        ]));

        return Helpers::success($order, 'Produit créée avec succès');
    }
}
