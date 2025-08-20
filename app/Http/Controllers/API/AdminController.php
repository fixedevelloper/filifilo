<?php


namespace App\Http\Controllers\API;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
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
    public function getDrivers(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = User::with([])->latest()->paginate($perPage, ['*'], 'page', $page);

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
    public function getProducts(Request $request)
    {
        $categories = [];
        $perPage = $request->input('per_page', 5); // nombre d'éléments par page
        $page = $request->input('page', 1); // numéro de la page

        $paginator = User::with([])->latest()->paginate($perPage, ['*'], 'page', $page);

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
}
