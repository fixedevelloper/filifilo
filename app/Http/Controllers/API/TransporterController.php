<?php


namespace App\Http\Controllers\API;


use App\Events\NewNotification;
use App\Events\TransporterPositionUpdated;
use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DriverPosition;
use App\Models\LineItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\TransporterPosition;
use App\Models\Vehicule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function updateTransporterPosition(Request $request)
    {
        $driverId = $request->input('driver_id');
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        // Stocker en base
        $pos = DriverPosition::updateOrCreate(
            ['driver_id' => $driverId],
            ['lat' => $lat, 'lng' => $lng]
        );
        $this->getLastCourseByDriver($driverId,$lat,$lng);
        return response()->json(['status' => 'ok']);
    }

    private function getLastCourseByDriver($driverId,$lat,$lng){
        $order=Order::query()->where(['transporter_id'=>$driverId,'status'=>Order::EN_COURS_LIVRAISON])->latest()->first();
        if (!is_null($order)){
            // Diffuser événement WebSocket
            event(new TransporterPositionUpdated([ 'transporterId'=>7,
                'lat'=>$lat,
                'lng'=>$lng]));
        }
    }
    public function getTransporterPosition($orderId)
    {
        $pos = TransporterPosition::where('order_id', $orderId)->latest()->first();

        if ($pos) {
            return response()->json(['lat' => $pos->lat, 'lng' => $pos->lng]);
        }

        return response()->json(null, 404);
    }
    public function getOrderStats()
    {
        $customer = Auth::user();
        $orders = Order::where('transporter_id', $customer->id)->get();

        // Commandes en attente (non livrées)
        $pending = $orders->where('status', '==', Order::EN_COURS_LIVRAISON)->count();

        // Commandes complétées (livrées)
        $complete = $orders->where('status', Order::LIVREE)->count();

        return Helpers::success([
            'deliveryStats' => [
                'pending'   => $pending,
                'completed' => $complete
            ]
        ]);
    }

    public function updateStatus(Request $request,$id)
    {
        $driver = Auth::user();

        // 1. Validation des données
        $validator = Validator::make($request->all(), [
            'status'   => 'required|string',
        ]);

        if ($validator->fails()) {
            $err = $validator->errors()->first();
            return Helpers::error($err);
        }

        logger($request->all());
        try {
            DB::transaction(function () use ($id, $request, $driver) {

                // 2. Récupération sécurisée avec verrouillage
                $order = Order::where('id', $id)
                    ->whereNull('transporter_id')
                    ->lockForUpdate()
                    ->first();

                if (is_null($order)) {
                    throw new \Exception('Commande déjà attribuée');
                }

                // 3. Mise à jour de la commande
                $order->update([
                    'status'         => $request->status,
                    'transporter_id' => $driver->id
                ]);

                // 4. Création de la notification
                $notification = Notification::create([
                    'user_id'       => $order->customer->id,
                    'username'      => $order->customer->first_name,
                    'profile_image' => $order->customer->profile_image,
                    'action_text'   => "Votre commande est en cours de livraison",
                    'time'          => Carbon::now()->diffForHumans(),
                    'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
                ]);

                // 5. Envoi temps réel
                broadcast(new NewNotification([
                    'user_id'       => $order->customer->id,
                    'username'      => $order->customer->first_name,
                    'profile_image' => $order->customer->profile_image,
                    'action_text'   => "Votre commande est en cours de livraison",
                    'time'          => $notification->time ?? Carbon::now()->diffForHumans(),
                    'thumbnail_url' => "https://images.unsplash.com/photo-1604908812273-2fdb7354bf9c"
                ]));
            });

            return Helpers::success(null, 'Commande attribuée avec succès');

        } catch (\Exception $e) {
            logger($e->getMessage());
            return Helpers::error($e->getMessage());
        }
    }
    public function createVehicule(Request $request)
    {
        $customer = Auth::user();

        // 1️⃣ Validation des champs
        $validator = Validator::make($request->all(), [
            'brand'       => 'required|string',
            'model'       => 'required|string',
            'color'       => 'nullable|string',
            'numberplate' => 'required|string',
            'milage'      => 'nullable|string',
            'passenger'   => 'nullable|integer|min:1',
            'type'        => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // ⚡ sécurisation image
        ]);

        if ($validator->fails()) {
            $err = $validator->errors()->first();
            return Helpers::error($err);
        }

        $validated = $validator->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicules', 'public');
        }

        DB::beginTransaction();

        try {
            // ⚡ Utiliser la relation OneToOne
            $vehicule = $customer->vehicule()->updateOrCreate(
                ['driver_id' => $customer->id], // condition de recherche
                [
                    'numberplate' => $validated['numberplate']?? null,
                    'brand'       => $validated['brand']?? null,
                    'model'       => $validated['model']?? null,
                    'color'       => $validated['color'] ?? null,
                    'milage'      => $validated['milage'] ?? null,
                    'passenger'   => $validated['passenger'] ?? 1,
                    'type'        => $validated['type'] ?? null,
                    'image'       => $imagePath,
                ]
            );


            DB::commit();

            return Helpers::success($vehicule, 'Véhicule créé avec succès ✅');
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du véhicule', [
                'message' => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création du véhicule');
        }
    }
    public function updateVehicule(Request $request)
    {
        $customer = Auth::user();

        // Vérifier si le user a déjà un véhicule
        $vehicule = $customer->vehicule;
        if (!$vehicule) {
            return Helpers::error("Aucun véhicule trouvé pour cet utilisateur.");
        }

        // 1️⃣ Validation des champs
        $validator = Validator::make($request->all(), [
            'brand'       => 'nullable|string',
            'model'       => 'nullable|string',
            'color'       => 'nullable|string',
            'numberplate' => 'nullable|string|unique:vehicules,numberplate,' . $vehicule->id,
            'milage'      => 'nullable|string',
            'passenger'   => 'nullable|integer|min:1',
            'type'        => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            $err = $validator->errors()->first();
            return Helpers::error($err);
        }

        $validated = $validator->validated();

        // 2️⃣ Gestion de l'image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicules', 'public');
            $validated['image'] = $imagePath;
        }

        DB::beginTransaction();
        try {
            // 3️⃣ Mise à jour du véhicule
            $vehicule->update($validated);

            DB::commit();
            return Helpers::success($vehicule, "Véhicule mis à jour avec succès ✅");
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du véhicule', [
                'message' => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la mise à jour du véhicule.');
        }
    }
    public function getMeVehicule()
    {
        $driver = Auth::user();
        $vehicule = Vehicule::where('driver_id', $driver->id)->latest()->first();

        return Helpers::success($vehicule);
    }
}
