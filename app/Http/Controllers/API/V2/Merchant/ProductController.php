<?php


namespace App\Http\Controllers\API\V2\Merchant;


use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index($storeID) {
        $store=Store::query()->find($storeID);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }

        $products = Product::where('store_id', $storeID)->orderBy('name', 'asc')->get()->map(function ($product) {
            return  new ProductResource($product);
        });

        return Helpers::success($products, 'Produits récupérés avec succès');
    }
    public function store(Request $request, $storeID)
    {
        // Get the currently authenticated user
        $cuser = Auth::user();
        $store = Store::find($storeID);

        // Check if the store exists and if the user is associated with the store
        if (is_null($store)) {
            return Helpers::error('Vous n\'êtes pas vendeur de ce magasin');
        }

        // Validate input data
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'price'       => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'ingredients' => 'required|string', // Expecting a comma-separated string
            'addons'      => 'required|string', // Expecting a comma-separated string
            'details'     => 'nullable|string',
            'delivery'    => 'nullable|string',
            'pickUp'      => 'nullable|string',
            'image'       => 'nullable|image|max:2048', // Limit image size to 2MB
        ]);

        if ($validator->fails()) {
            return Helpers::error($validator->errors()->first());
        }

        // Handle image upload if present
        $imageUrl = null;
        if ($request->hasFile('image')) {
            // Store the image in the 'products' directory
            $imagePath = $request->file('image')->store('products', 'public');
            $imageUrl = env('APP_URL') . Storage::url($imagePath); // Full URL
        }

        // Begin transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Split the comma-separated string into arrays
            $ingredients = array_map('trim', explode(',', $request->ingredients)); // Split and trim each item
            $addons = array_map('trim', explode(',', $request->addons)); // Split and trim each item

            // Convert arrays to JSON
            $ingredientsJson = json_encode($ingredients);
            $addonsJson = json_encode($addons);

            // Create the product record
            $product = Product::create([
                'name'        => $request->name,
                'price'       => $request->price,
                'description'     => $request->details,
                'store_id'    => $store->id,
                'category_id' => $request->category_id,
                'ingredients' => $ingredients,  // Store as JSON string
                'addons'      => $addons,       // Store as JSON string
                'imageUrl'    => $imageUrl,         // Optional image URL
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response with the created product data
            return Helpers::success($product, 'Produit créé avec succès');
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            // Log the error for debugging purposes
            Log::error('Erreur lors de la création du produit', [
                'message' => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
            ]);

            // Return a generic error message to the user
            return Helpers::error('Une erreur est survenue lors de la création du produit');
        }
    }

    public function show($id) {
        $product = Product::findOrFail($id);
        return Helpers::success(new ProductResource($product));
    }
    public function featured_products(Request $request,$storeID)
    {
        $customer = Auth::user();
        $store=Store::query()->find($storeID);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }

        $products = Product::where('store_id', $storeID)->orderBy('name', 'asc')->get()->map(function ($product) {
            return  new ProductResource($product);
        });

        return Helpers::success($products, 'Produits récupérés avec succès');
    }
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
