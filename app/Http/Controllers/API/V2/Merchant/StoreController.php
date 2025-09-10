<?php


namespace App\Http\Controllers\API\V2\Merchant;

use App\Helpers\api\Helpers;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function storeDefaut()
    {
        $user = Auth::user();
        $store = Store::query()->firstWhere('merchant_id', $user->merchant->id);
        if (is_null($store)) {
            return Helpers::success([
                'id' => null,
                'name' => null,
                'type' => null,
                'latitude' => null,
                'longitude' => null,
                'address' => null,
                'isOpen' => false
            ]);
        }
        return Helpers::success([
            'id' => $store->id,
            'name' => $store->name,
            'type' => $store->store_type,
            'latitude' => $store->latitude,
            'longitude' => $store->longitude,
            'address' => $store->address,
            'isOpen' => Helper::isStoreOpen($store->time_open, $store->time_close),
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        $stores = Store::query()->where('merchant_id', $user->merchant->id)->get()->map(function ($store) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'type' => $store->store_type,
                'latitude' => $store->latitude,
                'longitude' => $store->longitude,
                'address' => $store->address
            ];
        });
        return Helpers::success($stores);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|string',
            'address' => 'nullable|string',
            'time_close' => 'nullable|string',
            'time_open' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $validator->validated();
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stores', 'public'); // stockage relatif
            $imageUrl = env('APP_URL') . Storage::url($imagePath); // URL complète
        } else {
            $imageUrl = null;
        }
        $store = Store::create([
            'image_url' => $imageUrl,
            'city_id' => 1,
            'country_id' => 1,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'name' => $data['name'],
            'time_close' => $data['time_close'],
            'time_open' => $data['time_open'],
            'store_type' => $data['type'] == 'Boutique' ? 'shop' : $data['type'],
            'merchant_id' => $user->merchant->id,
        ]);
        return Helpers::success($store);
    }

    public function show($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }
}
