<?php


namespace App\Http\Controllers\API\V2\Admin;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\DriverPosition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function users()
    {
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
                'store_type' => $cat->store_type,
                'description' => $cat->description,
                'image' => $cat->image, // adapte 'url' selon ta colonne image
            ];
        }

        return response()->json([
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
            'brand' => 'required|string',
            'model' => 'required|string',
            'color' => 'nullable|string',
            'numberplate' => 'required|string|unique:vehicules,numberplate',
            'milage' => 'nullable|string',
            'passenger' => 'nullable|integer|min:1',
            'type' => 'nullable|string',
            'driver_id' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // ⚡ sécurisation image
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
            $vehicule = Vehicle::create([
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'color' => $validated['color'] ?? null,
                'numberplate' => $validated['numberplate'],
                'milage' => $validated['milage'] ?? null,
                'passenger' => $validated['passenger'] ?? 1,
                'type' => $validated['type'] ?? null,
                'driver_id' => $validated['driver_id'] ?? null,
                'image' => $imagePath,
            ]);

            DB::commit();

            return Helpers::success($vehicule, 'Véhicule créé avec succès ✅');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du véhicule', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création du véhicule');
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

        return response()->json([
            'data' => $categories,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->count(),
        ]);
    }

    public function driverOnline()
    {
        $items = DriverPosition::query()->get();
        $drivers = [];
        foreach ($items as $cat) {
            $drivers[] = [
                'id' => $cat->id,
                'name' => $cat->driver->name,
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
        if ($request->mode == 'country') {
            $country = Country::find($request->id);
            if (!$country) return Helpers::error('Pays introuvable');
            $latitude = $country->latitude;
            $longitude = $country->longitude;
        } elseif ($request->mode == 'city') {
            $city = City::find($request->id);
            if (!$city) return Helpers::error('Ville introuvable');
            $latitude = $city->latitude;
            $longitude = $city->longitude;
        } else {
            return Helpers::error('Aucun pays ou ville fourni');
        }

        // Sélection des drivers à proximité
        $items = Driver::query()
            ->select('drivers.*', 'drivers.current_latitude', 'drivers.current_longitude', DB::raw("
        (6371 * acos(
            cos(radians($latitude)) *
            cos(radians(drivers.current_latitude)) *
            cos(radians(drivers.current_longitude) - radians($longitude)) +
            sin(radians($latitude)) *
            sin(radians(driver_positions.lat))
        )) AS distance
    "))
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->get();


        // Format pour la map
        $drivers = $items->map(function ($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->user->name,
                'latitude' => $driver->current_latitude,
                'longitude' => $driver->current_longitude,
            ];
        });

        return Helpers::success($drivers);
    }

    public function drivers(Request $request)
    {
        $drivers = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Driver::query()->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $drivers[] = [
                'id' => $cat->id,
                'name' => $cat->user->name,
                'phone' => $cat->user->phone,
                'image' => $cat->image, // adapte 'url' selon ta colonne image
            ];
        }

        return response()->json([
            'data' => $drivers,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function customers(Request $request)
    {
        $customers = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Customer::query()->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $customers[] = [
                'id' => $cat->id,
                'name' => $cat->user->name,
                'phone' => $cat->user->phone,
                'email' => $cat->user->email,
                'image' => $cat->user->image_url, // adapte 'url' selon ta colonne image
            ];
        }

        return response()->json([
            'data' => $customers,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function getProducts(Request $request)
    {
        $items = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Product::with(['store'])->latest()->paginate($perPage, ['*'], 'page', $page);



        return response()->json([
            'data' => ProductResource::collection($paginator),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function stores(Request $request)
    {
        $items = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = Store::with(['merchant'])->latest()->paginate($perPage, ['*'], 'page', $page);

        foreach ($paginator->items() as $cat) {
            $items[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'phone' => $cat->phone,
                'image' => $cat->image_url,
                'type' => $cat->store_type,
                'active' => $cat->status,
                'vendor' => $cat->merchant == null ? ' ' : $cat->merchant->user->name,
            ];
        }

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function orders(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $paginator = Order::query()
            ->orderByDesc("created_at")
            ->paginate($perPage, ['*'], 'page', $page);

        $data = OrderResource::collection($paginator);

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(), // total réel de tous les éléments
        ]);

    }

    public function getInfoDriver($id)
    {
        $info = Driver::query()->find($id);

        $data = [
            'name' => $info->user->name,
            'phone' => $info->user->phone,
            'email' => $info->user->email,
            'latitude' => $info->current_latitude,
            'longitude' => $info->current_longitude
        ];

        return Helpers::success($data);
    }
}
