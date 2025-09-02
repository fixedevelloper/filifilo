<?php


namespace App\Http\Controllers\API\V2\Auth;


use App\Helpers\api\Helpers;
use App\Helpers\GeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            // ✅ Validation
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|email|unique:users,email',
                'password'    => 'required|string|min:6',
                'phone'    => 'required|string|min:6',
                'device_id'    => 'nullable|string',
                'user_type'   => 'nullable|in:customer,admin,merchant,driver',
            ]);

            // ✅ Transaction
            return DB::transaction(function () use ($validated) {
                // Création utilisateur
                $user = User::create([
                    'name'      => $validated['name'],
                    'email'     => $validated['email'],
                    'phone'     => $validated['phone'],
                    'password'  => Hash::make($validated['password']),
                    'user_type' => $validated['user_type'] ?? 'customer',
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                // Si c'est un customer -> on crée son profil et son adresse
                if ($user->user_type === 'customer') {
                    $customer = Customer::create([
                        'user_id' => $user->id,
                    ]);
                   // $geoData = GeoHelper::getAddressFromCoordinates($validated['latitude'], $validated['longitude']);

                    return Helpers::success([
                        'user_id'      => $user->id,
                        'access_token' => $token,
                        'token_type'   => 'Bearer',
                    ]);
                }

                if ($user->user_type === 'merchant') {
                    $merchant = Merchant::create([
                        'user_id' => $user->id,
                    ]);
                }
                if ($user->user_type === 'driver') {
                    $driver = Driver::create([
                        'user_id' => $user->id,
                        'device_id' => $validated['device_id']
                    ]);

                }
                // Autres types d’utilisateurs
                return Helpers::success([
                    'user_id'        => $user->id,
                    'access_token'=> $token,
                    'token_type'  => 'Bearer',
                ]);
            });
        } catch (ValidationException $e) {
            return Helpers::error($e->getMessage(), [
                'code'=>404,
                'details'=>''
            ]);
        } catch (\Exception $e) {
            return Helpers::error($e->getMessage(), [
                'code'=>500,
                'details'=>''
            ]);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'phone'=>'required|string',
            'password'=>'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return Helpers::error('Invalid credentials','Invalid credentials');
        }

       if ($request->has('user_type') && $request->user_type=='driver'){
            $user->driver->update(['device_id' => $request->device_id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return Helpers::success([
            'name'=>$user->name,
            'user_id'      => $user->id,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]) ;
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out']);
    }
}
