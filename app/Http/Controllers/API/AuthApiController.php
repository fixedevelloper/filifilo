<?php


namespace App\Http\Controllers\API;


use App\Helpers\api\Helpers;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthApiController
{

    public function login(Request $request)
    {
        $privateKey = file_get_contents('private.pem');
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        // Vérifie si un customer correspond à ces credentials

        if (!Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            return Helpers::error('Invalid credentials');

        }
        $customer = User::where('phone', $request->phone)
            ->first();
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

    public function register(Request $request)
    {
        $privateKey = file_get_contents('private.pem');
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        // Vérifie si un customer correspond à ces credentials


        $customer = User::where('phone', $request->phone)
            ->first();
        if ($customer) {
            return Helpers::error('Le telephone existe deja');
        }
        $customer = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'user_type' => 3,
            'photo' => null,
            'role' => 'VENDOR',
            'activate' => true,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);

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

    public function profile(Request $request)
    {

        $customer = $request->customer;

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
        $customer = $request->customer;

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
}
