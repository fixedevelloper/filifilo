<?php


namespace App\Http\Controllers\API\V2\Common;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    public function points($customerId)
    {

    }

    public function coupons($customerId)
    {
        if (!$customerId) {
            return Helpers::error('$customerId est requis', 400);
        }
        $store = Coupon::query()->where(['customer_id' => $customerId])->get();
        return Helpers::success($store, 'Produits récupérés avec succès');
    }

    public function couponByCode($code)
    {
        $user = Auth::user();
        if (!$code) {
            return Helpers::error('$customerId est requis', 400);
        }
        $coupon = Coupon::query()->firstWhere(['customer_id' => $user->customer->id]);
        return Helpers::success($coupon, 'Coupon récupéré avec succès');
    }

    public function applyCoupon(Request $request)
    {
        $user = Auth::user();

        $coupon = Coupon::where(['code' => $request->code, 'customer_id' => $user->customer->id,'status'=>'active'])->first();

        if (!$coupon || !$coupon->status == 'active') {
            return Helpers::error('Coupon invalide', 400);
        }

        $orderAmount = $request->order_amount;

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

        return Helpers::success([
            'success' => true,
            'discount' => $discount,
            'final_amount' => $orderAmount - $discount
        ]);
    }
}
