<?php


namespace App\Http\Controllers\API\V2\Customer;

use App\Events\NewNotification;
use App\Helpers\api\Helpers;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $orders = Order::where('customer_id', $user->customer->id)->orderByDesc("created_at")->get()->map(function ($order) {
            $lines = OrderItem::where('order_id', $order->id)->get()->map(function ($line) {
                return [
                    'id' => $line->id,
                    'name' => $line->product_name,
                    'quantity' => $line->quantity,
                    'price' => $line->unit_price,
                    'total_price' => $line->total_price,
                ];
            });
            return [
                'id' => $order->id,
                'reference' => $order->reference,
                'quantity' => $order->quantity,
                'status' => $order->status,
                'total_ttc' => $order->total_amount,
                'total' => $order->total_amount,
                'items' => $lines,
                'store_name' => $order->store->name,
                'date' => $order->created_at->toDateTimeString(),
            ];
        });

        return Helpers::success($orders, 'Commandes récupérés avec succès');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'delivery_address_id' => 'required|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            $err = null;
            foreach ($validator->errors()->all() as $error) {
                $err = $error;
            }
            return Helpers::error($err);
        }

        Log::info('Début de createOrder', [
            'customer_id' => $user->id ?? null,
            'email' => $user->email ?? 'non défini',
        ]);

        $items = $request->items;
        $total = 0.0;

        DB::beginTransaction();

        try {
            // Pré-calcul du total
            foreach ($items as $item) {
                $total += $item['total'];
            }
            $order = Order::create([
                'total_amount' => $total,
                'status' => Order::PENDING,
                'store_id' => $request->store_id,
                'customer_id' => $user->customer->id,
                'delivery_address_id' => $request->delivery_address_id,
                'payment_method_id' => $request->payment_method_id,
                'instructions' => $request->note,
                'payment_status' => $request->payment_status,
                'reference' => 'FF_' . Helper::generatenumber(),

            ]);
            $order->preparation_time = Helper::getDurationOSRM($order->store->latitude, $order->store->longitude, $order->deliveryAddress->latitude, $order->deliveryAddress->longitude);
            foreach ($items as $item) {
                Log::debug('Création de line item', ['item' => $item]);
                OrderItem::create([
                    'addons' => json_encode($item['addons']),
                    'instructions' => $item['instructions'],
                    'product_virtual' => $item['product_virtual'] ?? false,
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total'],
                    'unit_price' => $item['price'],
                    'order_id' => $order->id
                ]);
            }


            $notification = Notification::create([
                'order_id' => $order->id,
                'recipient_id' => $order->store->merchant_id,
                "recipient_type" => 'merchant',
                "message" => "Placed a new order",
                'title' => 'Placed a new order',
            ]);
            $notification_admin = Notification::create([
                'order_id' => $order->id,
                'recipient_id' => 1,
                "recipient_type" => 'admin',
                "message" => "Placed a new order",
                'title' => 'Placed a new order',
            ]);
                     broadcast(new NewNotification($notification_admin));
            broadcast(new NewNotification($notification));
            DB::commit();

            return Helpers::success([
                'reference' => $order->reference,
                'id' => $order->id,
            ], 'Commande créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la commande', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création de la commande.');
        }
    }

    public function show($id)
    {

        $order = Order::with(['store', 'orderItems', 'customer', 'deliveryAddress', 'latestDelivery'])->find($id);

        if (!$order) {
            return Helpers::error('Commande non trouvée');
        }

        logger($order);
        return Helpers::success(new OrderResource($order), 'Commande récupérée avec succès');
    }

    public function cancel($id)
    {

    }


}
