<?php


namespace App\Http\Controllers\API;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\DriverPosition;
use App\Models\Ingredient;
use App\Models\LineItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function createCategory(Request $request)
    {
        $customer = Auth::user();

        $validator=   Validator::make($request->all(),[
            'name' => 'required|string',
            'type' => 'nullable|string',
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
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        DB::beginTransaction();

        try {
            $product = Category::create([
                'name' => $request->name,
                'type' => $request->type,
                'image'=>$imagePath
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
    public function categories(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Category::with([])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $categories[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'image' => $cat->image , // adapte 'url' selon ta colonne image
            ];
        }

        return  response()->json([
            'data' => $categories,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }
    public function createVehicule(Request $request)
    {
        $customer = Auth::user();

        // 1️⃣ Validation des champs
        $validator = Validator::make($request->all(), [
            'brand'       => 'required|string',
            'model'       => 'required|string',
            'color'       => 'nullable|string',
            'numberplate' => 'required|string|unique:vehicules,numberplate',
            'milage'      => 'nullable|string',
            'passenger'   => 'nullable|integer|min:1',
            'type'        => 'nullable|string',
            'driver_id'   => 'nullable|integer',
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
            $vehicule = Vehicule::create([
                'brand'       => $validated['brand'],
                'model'       => $validated['model'],
                'color'       => $validated['color'] ?? null,
                'numberplate' => $validated['numberplate'],
                'milage'      => $validated['milage'] ?? null,
                'passenger'   => $validated['passenger'] ?? 1,
                'type'        => $validated['type'] ?? null,
                'driver_id'        => $validated['driver_id'] ?? null,
                'image'       => $imagePath,
            ]);

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

    public function createIngredient(Request $request)
    {
        $customer = Auth::user();

        $validator=   Validator::make($request->all(),[
            'name' => 'required|string',
            'type' => 'nullable|string',
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
            $imagePath = $request->file('image')->store('ingredients', 'public');
        }

        DB::beginTransaction();

        try {
            $product = Ingredient::create([
                'name' => $request->name,
                'type' => $request->type,
                'image'=>$imagePath
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
    public function ingredients(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Ingredient::with([])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $categories[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'image' => $cat->image,
            ];
        }

        return  response()->json([
            'data' => $categories,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }
    public function driverOnline(){
        $items=DriverPosition::query()->get();
        $drivers=[];
        foreach ($items as $cat) {
            $drivers[] = [
                'id' => $cat->id,
                'name' => $cat->driver->first_name.' '.$cat->driver->last_name,
                'latitude' => $cat->lat,
                'longitude' => $cat->lng,
            ];
        }
       return Helpers::success($drivers);
    }
    public function driverByOnline(Request $request)
    {
        $radius = 50; // distance en km
        $latitude = null;
        $longitude = null;

        // Déterminer le centre de recherche
        if ($request->mode=='country') {
            $country = Country::find($request->id);
            if (!$country) return Helpers::error('Pays introuvable');
            $latitude = $country->latitude;
            $longitude = $country->longitude;
        } elseif ($request->mode=='city') {
            $city = City::find($request->id);
            if (!$city) return Helpers::error('Ville introuvable');
            $latitude = $city->latitude;
            $longitude = $city->longitude;
        } else {
            return Helpers::error('Aucun pays ou ville fourni');
        }

        // Sélection des drivers à proximité
        $items = User::query()
            ->leftJoin('driver_positions', 'users.id', '=', 'driver_positions.driver_id')
            ->where('user_type', User::TYPE_SHIPPING)
            ->select('users.*', 'driver_positions.lat', 'driver_positions.lng', DB::raw("
        (6371 * acos(
            cos(radians($latitude)) *
            cos(radians(driver_positions.lat)) *
            cos(radians(driver_positions.lng) - radians($longitude)) +
            sin(radians($latitude)) *
            sin(radians(driver_positions.lat))
        )) AS distance
    "))
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->get();


        // Format pour la map
        $drivers = $items->map(function($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->first_name.' '.$driver->last_name,
                'latitude' => $driver->lat,
                'longitude' => $driver->lng,
            ];
        });

        return Helpers::success($drivers);
    }

    public function getDrivers(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = User::query()->where('user_type',User::TYPE_SHIPPING)->with([])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $categories[] = [
                'id' => $cat->id,
                'name' => $cat->first_name.' '.$cat->last_name,
                'phone' => $cat->type,
                'image' => $cat->image , // adapte 'url' selon ta colonne image
            ];
        }

        return  response()->json([
            'data' => $categories,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }
    public function getCustomers(Request $request)
    {
        $customers = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = User::query()->where('user_type',User::TYPE_CUSTOMER)->with([])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $customers[] = [
                'id' => $cat->id,
                'name' => $cat->first_name.' '.$cat->last_name,
                'phone' => $cat->phone,
                'email' => $cat->email,
                'image' => $cat->image , // adapte 'url' selon ta colonne image
            ];
        }

        return  response()->json([
            'data' => $customers,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }
    public function getProducts(Request $request)
    {
        $items = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Product::with(['store'])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $items[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'price' => $cat->price,
                'image' => $cat->imageUrl ,
                'stock' => $cat->stock ,
                'active' => $cat->is_active ,
                'store' => $cat->store->name ,
                'store_type' => $cat->store->type ,
            ];
        }

        return  response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }
    public function getStores(Request $request)
    {
        $items = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Store::with(['vendor'])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $items[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'phone' => $cat->phone,
                'image' => $cat->imageUrl ,
                'type' => $cat->type ,
                'active' => $cat->is_active ,
                'vendor' => $cat->vendor==null?' ': $cat->vendor->first_name.' '.$cat->vendor->last_name  ,
            ];
        }

        return  response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }
    public function getOrders(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $paginator = Order::query()
            ->orderByDesc("created_at")
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function ($order) {
            $lines = LineItem::where('order_id', $order->id)->get()->map(function ($line) {
                return [
                    'id' => $line->id,
                    'name' => $line->name,
                    'quantity' => $line->quantity,
                    'price' => $line->price,
                ];
            });

            return [
                'id' => $order->id,
                'reference' => $order->reference,
                'quantity' => $order->quantity,
                'status' => $order->status,
                'total_ttc' => $order->total_ttc,
                'total' => $order->total,
                'items' => $lines,
                'customer_name' => $order->customer->first_name.' '.$order->customer->last_name,
                'store_name' => $order->store->name,
                'date' => $order->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(), // total réel de tous les éléments
        ]);

    }

    public function getInfoDriver($id, Request $request)
    {
        $info = User::query()->find($id);

        $pendings = Order::query()
            ->where(function ($q) use ($id) {
                $q->where('transporter_id', $id)
                    ->whereIn('status', [Order::EN_LIVRAISON, Order::EN_COURS_LIVRAISON]);
            })
            ->get()
            ->map(function ($order) {
                $lines = LineItem::where('order_id', $order->id)->get()->map(function ($line) {
                    return [
                        'id'       => $line->id,
                        'name'     => $line->name,
                        'quantity' => $line->quantity,
                        'price'    => $line->price,
                    ];
                });

                return [
                    'id'         => $order->id,
                    'reference'  => $order->reference,
                    'quantity'   => $order->quantity,
                    'status'     => $order->status,
                    'total_ttc'  => $order->total_ttc,
                    'total'      => $order->total,
                    'items'      => $lines,
                    'store_name' => $order->store->name,
                    'date'       => $order->created_at->toDateTimeString(),
                ];
            });
        $position=DriverPosition::query()->firstWhere(['driver_id'=>$id]);
        $data = [
            'driver' => [
                'name'  => $info->first_name . ' ' . $info->last_name,
                'phone' => $info->phone,
                'email' => $info->email,
                'latitude'=>$position==null?0.0:$position->lat,
                'longitude'=>$position==null?0.0:$position->lng
            ],
            'pending_order' => $pendings
        ];

        return Helpers::success($data);
    }

}
