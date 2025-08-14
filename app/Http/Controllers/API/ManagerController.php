<?php


namespace App\Http\Controllers\API;


use App\Events\NewNotification;
use App\Helpers\api\Helpers;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\LineItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function getStore(Request $request){
        $customer = Auth::user();
        $store=Store::query()->firstWhere(['vendor_id'=>$customer->id]);
        return Helpers::success([
            'id'=>$store->id,
            'name'=>$store->name,
            'type'=>$store->type,
            'phone'=>$store->phone,
            'address'=>$store->address
        ], 'Commandes récupérés avec succès');
    }
    public function orders(Request $request)
    {
        $customer = Auth::user();
        $store=Store::query()->firstWhere(['vendor_id'=>$customer->id]);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }
        $orders = Order::where('store_id', $store->id)->orderByDesc("created_at")->get()->map(function ($order) {
            $lines=LineItem::where('order_id',$order->id)->get()->map(function ($line){
                return [
                    'id'          => $line->id,
                    'name'          => $line->name,
                    'quantity'          => $line->quantity,
                    'price'          => $line->price,
                ];
            });
            return [
                'id'          => $order->id,
                'reference'        => $order->reference,
                'quantity'       => $order->quantity,
                'status'   => $order->status,
                'total_ttc' => $order->total_ttc,
                'total' => $order->total,
                'items'=>$lines,
                'customer_name' => $order->customer->first_name.' '.$order->customer->last_name,
                'date' => $order->created_at->toDateTimeString(),
            ];
        });

        return Helpers::success($orders, 'Commandes récupérés avec succès');
    }
    public function products(Request $request)
    {
        $customer = Auth::user();
        $store=Store::query()->firstWhere(['vendor_id'=>$customer->id]);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }

        $products = Product::where('store_id', $store->id)->orderBy('name', 'asc')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image_url' => $product->imageUrl,
                'category_id' => $product->category_id,
                'store_id' => $product->store_id,
                'store_name' => $product->store->name,
                'category_name' => $product->category->name,
                'created_at' => $product->created_at->toDateTimeString(),
            ];
        });

        return Helpers::success($products, 'Produits récupérés avec succès');
    }
    public function getProduct(Request $request,$id)
    {
        $customer = Auth::user();
        $store=Store::query()->firstWhere(['vendor_id'=>$customer->id]);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }

        $product = Product::with(['store', 'category'])->findOrFail($id);

        $item = [
            'id' => $product->id,
            'name' => $product->name,
            'details' => $product->description,
            'price' => $product->price,
            'image_url' => $product->imageUrl,
            'category_id' => $product->category_id,
            'store_id' => $product->store_id,
            'store_name' => $product->store->name ?? null,
            'category_name' => $product->category->name ?? null,
            'created_at' => $product->created_at->toDateTimeString(),
        ];


        return Helpers::success($item, 'Produit récupérés avec succès');
    }
    public function createProduct(Request $request)
    {
        $customer = Auth::user();
        $store=Store::query()->firstWhere(['vendor_id'=>$customer->id]);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }
        $validator=   Validator::make($request->all(),[
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'ingredients' => 'required|string',
            'details' => 'nullable|string',
            'delivery' => 'nullable|string',
            'pickUp' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // max 2Mo par exemple
        ]);

        if ($validator->fails()) {
            $err = null;
            foreach ($validator->errors()->all() as $error) {
                $err = $error;
            }
            return Helpers::error($err);
        }
        $imagePath=null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        DB::beginTransaction();

        try {
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'store_id' => $store->id,
                'category_id' => $request->category_id,
                'imageUrl'=>$imagePath
            ]);

            DB::commit();

            return Helpers::success($product, 'Produit créée avec succès');

        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du produit', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création ddu produit');
        }
    }
    public function updateStatus(Request $request)
    {
        $customer = Auth::user();
        $store=Store::query()->firstWhere(['vendor_id'=>$customer->id]);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }
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
        if ($request->status == Order::EN_LIVRAISON) {

            $drivers = User::where('user_type', User::TYPE_SHIPPING)
                ->whereDoesntHave('orders', function ($query) {
                    $query->where('status', Order::EN_COURS_LIVRAISON);
                })
                ->get();

            foreach ($drivers as $driver) {

                // Création en BDD
                $notification = Notification::create([
                    'user_id'       => $driver->id,
                    'username'      => $driver->first_name,
                    'profile_image' => $driver->profile_image, // ✅ Image du client
                    'action_text'   => "Placed a new order",
                    'time'          => now()->diffForHumans(), // ou via accessoire
                    'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
                ]);

                // Envoi en temps réel
                broadcast(new NewNotification([
                    'user_id'       => $driver->id,
                    'username'      => $driver->first_name, // ✅ cohérent
                    'profile_image' => $driver->profile_image,
                    'action_text'   => "Placed a new order",
                    'time'          => $notification->time_ago, // Accessoire du modèle
                    'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
                ]));
            }
        }

        return Helpers::success($order, 'Produit créée avec succès');
    }
    public function revenueDetails()
    {
        $data = [
            'totalRevenue' => '12345.67',
            'chartData' => [
                ['label' => 'Jan', 'value' => 5000],
                ['label' => 'Fév', 'value' => 6400],
                ['label' => 'Mar', 'value' => 7200],
            ]
        ];

        return Helpers::success($data, 'Produit créée avec succès');
    }
    public function getChartData(Request $request)
    {
        $filter = $request->query('filter', 'Mois'); // valeur par défaut "Mois"

        // 📊 Simulation de données selon le filtre
/*        if ($filter === 'Jour') {
            $chartData = [
                ['label' => '8AM',  'value' => 120],
                ['label' => '10AM', 'value' => 240],
                ['label' => '12PM', 'value' => 500],
                ['label' => '2PM',  'value' => 320],
                ['label' => '4PM',  'value' => 450],
            ];
            $transactions = [
                ['time' => '12:00', 'amount' => 500],
                ['time' => '10:30', 'amount' => 240],
                ['time' => '09:15', 'amount' => 120],
            ];
        } elseif ($filter === 'Semaine') {
            $chartData = [
                ['label' => 'Lun', 'value' => 800],
                ['label' => 'Mar', 'value' => 950],
                ['label' => 'Mer', 'value' => 720],
                ['label' => 'Jeu', 'value' => 1100],
                ['label' => 'Ven', 'value' => 870],
            ];
            $transactions = [
                ['time' => 'Lundi', 'amount' => 800],
                ['time' => 'Mardi', 'amount' => 950],
                ['time' => 'Mercredi', 'amount' => 720],
            ];
        } else { // Mois
            $chartData = [
                ['label' => 'Jan', 'value' => 5000],
                ['label' => 'Fév', 'value' => 6400],
                ['label' => 'Mar', 'value' => 7200],
                ['label' => 'Avr', 'value' => 6800],
                ['label' => 'Mai', 'value' => 7300],
            ];
            $transactions = [
                ['time' => 'Janvier', 'amount' => 5000],
                ['time' => 'Février', 'amount' => 6400],
                ['time' => 'Mars', 'amount' => 7200],
            ];
        }*/
        if ($filter === 'Jour') {
            $chartData = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%H:%i') as label"),
                DB::raw("SUM(total) as value")
            )
                ->whereDate('created_at', Carbon::today())
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $transactions = Order::whereDate('created_at', Carbon::today())
                ->orderByDesc('created_at')
                ->get(['created_at', 'total'])
                ->map(function($order) {
                    return [
                        'time' => $order->created_at->format('H:i'),
                        'amount' => (float) $order->amount
                    ];
                });

        } elseif ($filter === 'Semaine') {
            $chartData = Order::select(
                DB::raw("DAYNAME(created_at) as label"),
                DB::raw("SUM(total) as value")
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
                DB::raw("SUM(total) as value")
            )
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('label')
                ->orderByRaw("MONTH(MIN(created_at))")
                ->get();

            $transactions = Order::whereYear('created_at', Carbon::now()->year)
                ->orderByDesc('created_at')
                ->get(['created_at', 'total'])
                ->map(function($order) {
                    return [
                        'time' => $order->created_at->format('F'),
                        'amount' => (float) $order->amount
                    ];
                });
        }
        logger($transactions);
        return Helpers::success([
            'chartData' => $chartData,
            'transactions' => $transactions
        ], 'Produit créée avec succès');
    }
}
