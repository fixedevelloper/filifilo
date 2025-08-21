<?php


namespace App\Http\Controllers\API;


use App\Helpers\api\Helpers;
use App\Helpers\Helper;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController
{
    public function categories(Request $request)
    {
        if ($request->type == 'shop') {
            $lists = Category::query()->where(['type' => Helper::TYPESTORESHOP])->get();
        } else {
            $lists = Category::query()->where(['type' => Helper::TYPESTORERESTAURANT])->get();
        }

        $message = 'categories get successful';
        return Helpers::success($lists, $message);
    }

    public function countries(Request $request)
    {
        $lists = Country::query()->where([])->get();
        $message = 'ingredients get successful';
        return Helpers::success($lists, $message);
    }

    public function cities(Request $request, $id)
    {
        $lists = City::query()->where(['country_id' => $id])->get();
        $message = 'ingredients get successful';
        return Helpers::success($lists, $message);
    }

    public function ingredients(Request $request)
    {
        $lists = Ingredient::query()->where([])->get();

        $message = 'ingredients get successful';
        return Helpers::success($lists, $message);
    }

    public function stores(Request $request)
    {
        $type = $request->type;
        // Choisir le type de store selon le paramètre
        $storeType = $type === 'SHOP'
            ? Helper::TYPESTORESHOP
            : Helper::TYPESTORERESTAURANT;

        // Requête avec tri alphabétique
        $stores = Store::where('type', $storeType)
            ->orderBy('name', 'asc')
            ->get();

        return Helpers::success($stores, 'Stores retrieved successfully');
    }

    public function products(Request $request, $storeId)
    {
        //$storeId = $request->store_id;
        $customer = Auth::user();
        // Optionnel : validation de l'entrée
        if (!$storeId) {
            return Helpers::error('store_id est requis', 400);
        }

        $products = Product::where('store_id', $storeId)->orderBy('name', 'asc')->get()->map(function ($product) {
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

    public function getDeatailStore(Request $request, $storeId)
    {
        logger($storeId);
        //$storeId = $request->store_id;
        $customer = Auth::user();
        // Optionnel : validation de l'entrée
        if (!$storeId) {
            return Helpers::error('store_id est requis', 400);
        }
        $store = Store::query()->find($storeId);
        return Helpers::success($store, 'Produits récupérés avec succès');
    }

    public function search(Request $request)
    {
        //$storeId = $request->store_id;
        $customer = Auth::user();
        $search = $request->get('search');
        if (!$search) {
            return Helpers::error('store_id est requis', 400);
        }
        $products = Product::with(['store', 'category'])
            ->where('name', 'like', '%' . $search . '%') // Only use LIKE if store_id is a string
            ->orderBy('name', 'asc')->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->imageUrl,
                    'category_id' => $product->category_id,
                    'store_id' => $product->store_id,
                    'store_name' => $product->store->name ?? null,
                    'category_name' => $product->category->name ?? null,
                    'created_at' => $product->created_at->toDateTimeString(),
                ];
            });
        $restaurants = Store::where('name', 'like', '%' . $search . '%')
            ->where('type', 'RESTAURANT')
            ->orderBy('name', 'asc')->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'latitude' => $product->latitude,
                    'longitude' => $product->longitude,
                    'image_url' => $product->imageUrl,
                    'created_at' => $product->created_at->toDateTimeString(),
                ];
            });
        $shops = Store::where('name', 'like', '%' . $search . '%')
            ->orderBy('name', 'asc')->where('type', 'shop')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'latitude' => $product->latitude,
                    'longitude' => $product->longitude,
                    'image_url' => $product->imageUrl,
                    'created_at' => $product->created_at->toDateTimeString(),
                ];
            });

        return Helpers::success([
            'products' => $products,
            'menus' => $products,
            'shops' => $shops,
            'restaurants' => $restaurants
        ], 'Produits récupérés avec succès');
    }
}
