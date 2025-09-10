<?php


namespace App\Helpers;

use App\Events\NewNotification;
use App\Models\{Order, OrderItem, Coupon, LoyaltyPoint, Notification};
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder($user, $request)
    {
        $items = $request->items;
        $discount = $request->discount ?? 0;
        $usePoints = (int) $request->input('use_points', 0);

        $total = array_sum(array_column($items, 'total'));
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

            $order->preparation_time = Helper::getDurationOSRM(
                $order->store->latitude,
                $order->store->longitude,
                $order->deliveryAddress->latitude,
                $order->deliveryAddress->longitude
            )['minutes'];
            $order->save();

            // Création des items
            foreach ($items as $item) {
                OrderItem::create([
                    'addons' => json_encode($item['addons']),
                    'instructions' => $item['instructions'] ?? '',
                    'product_virtual' => $item['product_virtual'] ?? false,
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total'],
                    'unit_price' => $item['price'],
                    'order_id' => $order->id,
                ]);
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
            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la commande', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
