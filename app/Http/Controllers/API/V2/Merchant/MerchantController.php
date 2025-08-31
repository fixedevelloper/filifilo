<?php


namespace App\Http\Controllers\API\V2\Merchant;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    public function getDashboard(Request $request, $storeId)
    {
        $user = Auth::user();
        $store = Store::find($storeId);

        if (is_null($store)) {
            return Helpers::error("Vous n'êtes pas vendeur");
        }

        $statuses = [
            'pendingOrders' => Order::PENDING,
            'runingsOrders' => Order::PREPARATION,
        ];

        $ordersData = [];

        foreach ($statuses as $key => $status) {
            $orders = Order::where('store_id', $storeId)
                ->where('status', $status)
                ->with(['orderItems', 'customer']) // Eager loading
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($order) => $this->formatOrder($order));

            $ordersData[$key] = $orders;
        }

        return Helpers::success($ordersData, 'Commandes récupérées avec succès');
    }
    public function revenueDetails()
    {
        $data = [
            'totalRevenue' => '12345.67',
            'chartData' => [
                ['label' => 'Jan', 'value' => 2000],
                ['label' => 'Fév', 'value' => 6400],
                ['label' => 'Mar', 'value' => 7200],
            ]
        ];

        return Helpers::success($data, 'Produit créée avec succès');
    }
    public function getChartData(Request $request)
    {
        $filter = $request->query('filter', 'Mois'); // valeur par défaut "Mois"

        if ($filter === 'Jour') {
            $chartData = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%H:%i') as label"),
                DB::raw("SUM(total_amount) as value")
            )
                ->whereDate('created_at', Carbon::today())
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $transactions = Order::whereDate('created_at', Carbon::today())
                ->orderByDesc('created_at')
                ->get(['created_at', 'total_amount'])
                ->map(function($order) {
                    return [
                        'time' => $order->created_at->format('H:i'),
                        'amount' => (float) $order->amount
                    ];
                });

        } elseif ($filter === 'Semaine') {
            $chartData = Order::select(
                DB::raw("DAYNAME(created_at) as label"),
                DB::raw("SUM(total_amount) as value")
            )
                ->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->groupBy('label')
                ->orderByRaw("FIELD(label, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();

            $transactions = Order::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
                ->orderByDesc('created_at')
                ->get(['created_at', 'total'])
                ->map(function($order) {
                    return [
                        'time' => $order->created_at->format('l'),
                        'amount' => (float) $order->amount
                    ];
                });

        } else { // Mois
            $chartData = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%b') as label"),
                DB::raw("SUM(total_amount) as value")
            )
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('label')
                ->orderByRaw("MONTH(MIN(created_at))")
                ->get();

            $transactions = Order::whereYear('created_at', Carbon::now()->year)
                ->orderByDesc('created_at')
                ->get(['created_at', 'total_amount'])
                ->map(function($order) {
                    return [
                        'time' => $order->created_at->format('F'),
                        'amount' => (float) $order->amount
                    ];
                });
        }
        return Helpers::success([
            'chartData' => $chartData,
            'transactions' => $transactions
        ], 'Produit créée avec succès');
    }

    public function profile() {
        $user = Auth::user();

        if (!$user) {
            return Helpers::error('$customer est requis', 400);
        }
        return Helpers::success([
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'balance' => 0.0,
            'date_birth' => date('Y-m-d')
        ], 'Profile récupérés avec succès');
    }
    public function updateProfile(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
        ]);
        $customer = $request->customer;

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);


        return Helpers::success([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => 0.0,
            'date_birth' => date('Y-m-d')
        ]);
    }
    private function formatOrder(Order $order)
    {
        return [
            'id'            => $order->id,
            'reference'     => $order->reference,
            'quantity'      => $order->quantity,
            'status'        => $order->status,
            'total_ttc'     => $order->total_ttc,
            'total'         => $order->total,
            'preparation_time'=> $order->preparation_time,
            'instructions'=> $order->instructions,
            'customer_name'    => $order->customer->user->name ?? '',
            'shipping_address' => $order->deliveryAddress->label ?? '',
            'items'         => $order->orderItems->map(fn($item) => [
                'id'        => $item->id,
                'name'      => $item->product_name,
                'quantity'  => $item->quantity,
                'price'     => $item->unit_price,
            ]),
        'date'          => $order->created_at->toDateString(),
    ];
}

}
