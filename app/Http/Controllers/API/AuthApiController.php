<?php


namespace App\Http\Controllers\API;


use App\Helpers\api\Helpers;
use App\Models\Store;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthApiController
{

    public function login(Request $req)
    {
        $req->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
            'user_type' => 'required',
        ]);

        // Vérifie si un customer correspond à ces credentials

        if (!Auth::attempt(['phone' => $req->phone, 'password' => $req->password])) {
            return Helpers::error('Invalid credentials');

        }
        $customer=null;
        switch ($req->user_type){
            case User::TYPE_CUSTOMER:
                $customer = User::where(['phone'=>$req->phone,'user_type'=>User::TYPE_CUSTOMER])
                    ->first();
                break;
            case User::TYPE_SHIPPING:
                $customer = User::where(['phone'=>$req->phone,'user_type'=>User::TYPE_SHIPPING])
                    ->first();
                break;
            case User::TYPE_VENDOR:
                $customer = User::where(['phone'=>$req->phone,'user_type'=>User::TYPE_VENDOR])
                    ->first();
        }


        if (!$customer) {
            return Helpers::error('Invalid credentials');
        }
        $credentials = $req->only('phone', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
        return Helpers::success([
            'name'=>$customer->first_name.' '.$customer->last_name,
            'phone'=>$customer->phone,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
    public function refresh()
    {
        return response()->json([
            'access_token' => auth('api')->refresh(),
            'token_type' => 'bearer'
        ]);
    }
    public function register(Request $request)
    {
        // ✅ Validation centralisée
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:'.User::TYPE_VENDOR.','.User::TYPE_CUSTOMER,
            'store_name' => 'required_if:user_type,'.User::TYPE_VENDOR,
            'store_type' => 'required_if:user_type,'.User::TYPE_VENDOR,
            'store_address' => 'nullable|string',
            'store_phone' => 'nullable|string|max:20',
            'store_latitude' => 'nullable|numeric',
            'store_longitude' => 'nullable|numeric',
            'store_image' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            // ✅ Création de l’utilisateur
            $customer = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'user_type' => $validated['user_type'],
                'photo' => null,
                'role' => $validated['user_type'] === User::TYPE_VENDOR ? 'VENDOR' : 'CUSTOMER',
                'activate' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
            ]);

            // ✅ Création du store si vendeur
            if ($validated['user_type'] === User::TYPE_VENDOR) {
                $imagePath = null;
                if ($request->hasFile('store_image')) {
                    // Sauvegarde dans storage/app/public/stores
                    $imagePath = $request->file('store_image')->store('stores', 'public');
                }
                Store::create([
                    'latitude' => $validated['store_latitude'] ?? null,
                    'longitude' => $validated['store_longitude'] ?? null,
                    'name' => $validated['store_name'],
                    'type' => strtolower($validated['store_type']) === 'restaurant' ? 'RESTAURANT' : 'SHOP',
                    'address' => $validated['store_address'] ?? null,
                    'phone' => $validated['store_phone'] ?? null,
                    'vendor_id' => $customer->id,
                    'imageUrl' => $imagePath ? asset('storage/' . $imagePath) : null
                ]);
            }

            DB::commit();

            // ✅ Connexion auto après inscription
            $token = auth('api')->attempt([
                'phone' => $validated['phone'],
                'password' => $validated['password']
            ]);

            if (! $token) {
                return Helpers::error('Impossible de générer le token', 401);
            }

            return Helpers::success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return Helpers::error('Erreur lors de l’inscription : '.$e->getMessage(), 500);
        }
    }

    public function login2(Request $request)
    {
        $privateKey = file_get_contents('private.pem');
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
            'user_type' => 'required',
        ]);

        // Vérifie si un customer correspond à ces credentials

        if (!Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            return Helpers::error('Invalid credentials');

        }
        $customer=null;
        switch ($request->user_type){
            case User::TYPE_CUSTOMER:
                $customer = User::where(['phone'=>$request->phone,'user_type'=>User::TYPE_CUSTOMER])
                    ->first();
                break;
            case User::TYPE_SHIPPING:
                $customer = User::where(['phone'=>$request->phone,'user_type'=>User::TYPE_SHIPPING])
                    ->first();
                break;
            case User::TYPE_VENDOR:
                $customer = User::where(['phone'=>$request->phone,'user_type'=>User::TYPE_VENDOR])
                    ->first();
        }


        if (!$customer) {
            return Helpers::error('Invalid credentials');
        }

        // Génère un JWT signé avec SA clé privée
        $payload = [
            'iss' => 'wtc_api',
            'sub' => $customer->id,
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');

        return Helpers::success([
            'access_token' => $jwt,
            'token_type' => 'bearer',
            'expires_in' => 3600
        ]);
    }
    public function register2(Request $request)
    {
        $privateKey = file_get_contents('private.pem');
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'password' => 'required|string',
            'user_type' => 'required',
        ]);
        $customer = User::where('phone', $request->phone)
            ->first();
        if ($customer) {
            return Helpers::error('Le telephone existe deja');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        $customer = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'user_type' => $request->user_type,
            'photo' => null,
            'role' => 'VENDOR',
            'activate' => true,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);
        if ($request->user_type==User::TYPE_VENDOR){
            $store=Store::create([
                'latitude' => $request->store_latitude,
                'longitude' => $request->store_longitude,
                'name' => $request->store_name,
                'type' => $request->store_type=='restaurant'?'RESTAURANT':'SHOP',
                'address' => $request->store_address,
                'phone' => $request->store_phone,
                'vendor_id' => $customer->id,
                'imageUrl'=>$request->imageUrl
            ]);
        }
        \Illuminate\Support\Facades\DB::commit();

        $credentials = $request->only('phone', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
        return Helpers::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 3600
        ]);
    }

    public function profile(Request $request)
    {

        $customer = Auth::user();

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        return Helpers::success([
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => $customer->sold,
            'date_birth' => date('Y-m-d')
        ], 'Profile récupérés avec succès');
    }

    public function updateProfile(Request $request)
    {

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);
        $customer = $request->customer;

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);


        return Helpers::success([
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => $customer->sold,
            'date_birth' => date('Y-m-d')
        ]);
    }

    public function changePassword(Request $request)
    {

        $request->validate([
            'new_password' => 'required|string',
            'password' => 'required|string',
        ]);
        $customer = Auth::user();

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        if (!Auth::attempt(['phone' => $customer->phone, 'password' => $request->password])) {
            return Helpers::error('Invalid credentials');

        }
        $customer->update([
            'password' => Hash::make($request->new_password)

        ]);

        return Helpers::success([
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => $customer->sold,
            'date_birth' => date('Y-m-d')
        ]);
    }
    public function authenticateBroacast(Request $request)
    {
        $publicKey = file_get_contents('public.pem');
        // Récupère les headers (par ex. Authorization Bearer JWT)
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        // Valide ton JWT ici (firebase/php-jwt, ou autre)
        try {
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
         //   $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            // Simuler un user
            $user = (object)[
                'id' => $decoded->sub,
                'name' => $decoded->name ?? null,
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Récupère les données envoyées par Pusher
        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        // Vérifie l’autorisation sur le canal, par exemple ici : private-notifications.{userId}
        if (preg_match('/^private-notifications\.(\d+)$/', $channelName, $matches)) {
            $userId = (int)$matches[1];
            if ($user->id !== $userId) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        } else {
            // Peut gérer d’autres canaux ou refuser par défaut
            return response()->json(['error' => 'Forbidden2'], 403);
        }

        // Crée la signature attendue par Pusher (Auth signature)
        $appKey = env('PUSHER_APP_KEY');
        $appSecret = env('PUSHER_APP_SECRET');

        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $appSecret);

        $auth = $appKey . ':' . $signature;

        return response()->json(['auth' => $auth]);
    }
}
