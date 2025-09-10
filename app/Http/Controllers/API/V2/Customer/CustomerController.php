<?php


namespace App\Http\Controllers\API\V2\Customer;

use App\Helpers\api\Helpers;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Drink;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
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
    public function dashboard(Request $request) {
        $restaurants=Store::query()->where(['store_type'=>'restaurant'])->limit(20)->get()->map(function ($store){
           return [
              'name' =>$store->name,
               'latitude' =>$store->latitude,
               'longitude' =>$store->longitude,
               'image_url' =>$store->image_url,
           ] ;
        });
        $shops=Store::query()->where(['store_type'=>'shop'])->limit(20)->get()->map(function ($store){
            return [
                'name' =>$store->name,
                'latitude' =>$store->latitude,
                'longitude' =>$store->longitude,
                'image_url' =>$store->image_url,
            ] ;
        });
        return Helpers::success([
           'restaurants'=>$restaurants,
           'shops'=>$shops
        ]);
    }
    public function storeByType(Request $request) {
        $type = $request->type;
        // Choisir le type de store selon le paramètre
        $storeType = $type === 'SHOP'
            ? Helper::TYPESTORESHOP
            : Helper::TYPESTORERESTAURANT;
        $stores = Store::where('store_type', $type)
            ->orderBy('name', 'asc')
            ->get()->map(function ($store){
                return [
                    'id' =>$store->id,
                    'name' =>$store->name,
                    'latitude' =>$store->latitude,
                    'longitude' =>$store->longitude,
                    'image_url' =>$store->image_url,
                    'isOpen'=>Helper::isStoreOpen($store->time_open,$store->time_close),
                ] ;
            });

        return Helpers::success($stores, 'Stores retrieved successfully');
    }
    public function storeId(Request $request, $storeId)
    {
        if (!$storeId) {
            return Helpers::error('store_id est requis', 400);
        }
        $store = Store::query()->find($storeId);
        return Helpers::success([
            'id' =>$store->id,
            'name' =>$store->name,
            'latitude' =>$store->latitude,
            'longitude' =>$store->longitude,
            'image_url' =>$store->image_url,
            'isOpen'=>Helper::isStoreOpen($store->time_open,$store->time_close),
        ], 'Produits récupérés avec succès');
    }

    public function search(Request $request)
    {
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
                    'description' => $product->description,
                    'image_url' => $product->image_url,
                    'category_id' => $product->category_id,
                    'store_id' => $product->store_id,
                    'store_name' => $product->store->name ?? null,
                    'category_name' => $product->category->name ?? null,
                    'created_at' => $product->created_at->toDateTimeString(),
                ];
            });
        $restaurants = Store::where('name', 'like', '%' . $search . '%')
            ->where('store_type', 'restaurant')
            ->orderBy('name', 'asc')->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude,
                    'image_url' => $store->image_url,
                    'isOpen'=>Helper::isStoreOpen($store->time_open,$store->time_close),
                ];
            });
        $shops = Store::where('name', 'like', '%' . $search . '%')
            ->orderBy('name', 'asc')->where('store_type', 'shop')
            ->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude,
                    'image_url' => $store->image_url,
                    'isOpen'=>Helper::isStoreOpen($store->time_open,$store->time_close),
                ];
            });

        return Helpers::success([
            'products' => $products,
            'menus' => $products,
            'shops' => $shops,
            'restaurants' => $restaurants
        ], 'Produits récupérés avec succès');
    }
    public function products(Request $request, $storeId)
    {
        if (!$storeId) {
            return Helpers::error('store_id est requis', 400);
        }

        $products = Product::where('store_id', $storeId)->orderBy('name', 'asc')->get();

        return Helpers::success(ProductResource::collection($products), 'Produits récupérés avec succès');
    }
    public function drinks(Request $request, $storeId)
    {
        $store=Store::query()->find($storeId);
        if (is_null($store)){
            return Helpers::error('Vous n etes pas vendeur');
        }

        $products = Drink::where('store_id', $storeId)->orderBy('name', 'asc')->get()->map(function ($product) {
            return  [
                'id'=>$product->id,
                'name'=>$product->name,
                'price'=>$product->price,
                'imageUrl'=>$product->imageUrl,
            ];
        });

        return Helpers::success($products, 'Produits récupérés avec succès');
    }
    public function updateProfile(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
        ]);
        $customer = Auth::user();

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
    public function sendCodeVerifyPhone(Request $request) {
        $request->validate([
            'phone' => 'required|string',
        ]);
        $customer = Auth::user();

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }



        return Helpers::success();
    }
}
