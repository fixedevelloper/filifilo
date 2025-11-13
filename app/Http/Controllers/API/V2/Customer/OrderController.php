<?php


namespace App\Http\Controllers\API\V2\Customer;

use App\Events\NewNotification;
use App\Helpers\api\Helpers;
use App\Helpers\FCMService;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\LoyaltyPoint;
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
            return Helpers::error($validator->errors()->first());
        }

        Log::info('Début de createOrder', [
            'customer_id' => $user->id ?? null,
            'email' => $user->email ?? 'non défini',
        ]);

        $items = $request->items;
        $discount = $request->discount ?? 0;
        $usePoints = (int) $request->input('use_points', 0);

       // $total = array_sum(array_column($items, 'total'));
        $total = $request->total;
        $availablePoints = $user->customer->availablePoints();
        $pointsToUse = min($usePoints, $availablePoints);
        $discountFromPoints = $pointsToUse * 100; // 1 point = 100 F

        DB::beginTransaction();

        try {
            // Création de la commande
            $order = Order::create([
                'total_amount' => $total,
                'discount_amount' => $discount + $discountFromPoints,
                'final_amount' => max(0, $total - ($discount + $discountFromPoints)),
                'status' => Order::PENDING,
                'store_id' => $request->store_id,
                'customer_id' => $user->customer->id,
                'delivery_address_id' => $request->delivery_address_id,
                'payment_method_id' => $request->payment_method_id,
                'instructions' => $request->note,
                'payment_status' => $request->payment_status,
                'reference' => 'FF_' . Helper::generatenumber(),
            ]);

            $order->delivery_time = Helper::getDurationOSRM(
                $order->store->latitude,
                $order->store->longitude,
                $order->deliveryAddress->latitude,
                $order->deliveryAddress->longitude
            )['minutes'];
            $order->save();
            $preparingTime=0;
            // Création des items
            foreach ($items as $item) {
                $preparingTime+=$item['preparing_time'];
                $orderItem= OrderItem::create([
                    'addons' => json_encode($item['addons']),
                    'supplements' => json_encode($item['supplements']),
                    'instructions' => $item['instructions'] ?? '',
                    'product_virtual' => $item['product_virtual'] ?? false,
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total'],
                    'unit_price' => $item['price'],
                    'order_id' => $order->id,
                ]);
                // Attacher les boissons si elles existent
                if (!empty($item['drinks'])) {
                    foreach ($item['drinks'] as $drink) {
                        // Si $drink est un objet Drink ou tableau avec 'id' et 'quantity'
                        $orderItem->drinks()->attach($drink['id'], [
                            'quantity' => $drink['quantity'] ?? 1
                        ]);
                    }
                }

            }

            // Application du coupon
            if ($request->filled('code_promo')) {
                $coupon = Coupon::where([
                    'code' => $request->code_promo,
                    'customer_id' => $user->customer->id,
                    'status' => 'active'
                ])->first();

                if ($coupon) {
                    $order->update(['coupon_id' => $coupon->id]);
                    $coupon->update(['status' => 'used']);
                }
            }
            $order->update(['preparing_time' => $preparingTime]);
            // Attribution des points
            $earnedPoints = floor($order->final_amount / 100);
            if ($earnedPoints > 0) {
                LoyaltyPoint::create([
                    'customer_id' => $user->customer->id,
                    'order_id' => $order->id,
                    'points' => $earnedPoints,
                    'type' => 'earned',
                    'expiry_date' => now()->addMonths(3),
                ]);
            }

            if ($pointsToUse > 0) {
                LoyaltyPoint::create([
                    'customer_id' => $user->customer->id,
                    'order_id' => $order->id,
                    'points' => $pointsToUse,
                    'type' => 'spent',
                ]);
            }

            // Notifications
            $notifications = [
                [
                    'recipient_id' => $order->store->merchant_id,
                    'recipient_type' => 'merchant',
                    'message' => 'Nouvelle commande',
                    'title' => 'Nouvelle commande',
                ],
                [
                    'recipient_id' => 1,
                    'recipient_type' => 'admin',
                    'message' => 'Nouvelle commande',
                    'title' => 'Nouvelle commande',
                ]
            ];

            foreach ($notifications as $notifData) {
                $notif = Notification::create(array_merge($notifData, ['order_id' => $order->id]));
                broadcast(new NewNotification($notif));
            }

            FCMService::sendKer($user->fcm_token, 'Nouvelle commande', 'Une nouvelle commande a été passée');

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
        $resource=new OrderResource($order);
        logger(json_encode($resource));
        return Helpers::success($resource, 'Commande récupérée avec succès');
    }

    public function cancel($id)
    {

    }

    private function applyCodePromo($code,$orderAmount){
        $user = Auth::user();

        $coupon = Coupon::where(['code' => $code, 'customer_id' => $user->customer->id])->first();

        if (!$coupon || !$coupon->status == 'active') {
            return Helpers::error('Coupon invalide', 400);
        }


        if ($orderAmount < $coupon->min_order_amount) {
            return Helpers::error('Montant minimum non atteint', 400);
        }

        if (now()->gt($coupon->expiry_date)) {
            return Helpers::error('Coupon expiré', 400);
        }

        // Calcul remise
        $discount = $coupon->discount_type === 'pourcentage'
            ? min($orderAmount * $coupon->discount_value / 100, $coupon->max_discount ?? INF)
            : $coupon->discount_value;
        return $discount;

    }

}
