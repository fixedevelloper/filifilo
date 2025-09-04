<?php


namespace App\Http\Controllers\API\V2\Auth;


use App\Helpers\api\Helpers;
use App\Helpers\GeoHelper;
use App\Helpers\WhatsappService;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    private  $whatsappService;

    /**
     * PasswordController constructor.
     * @param $whatsappService
     */
    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function register(Request $request)
    {
        try {
            // ✅ Validation des entrées
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|email|unique:users,email',
                'password'    => 'required|string|min:6',
                'phone'       => 'required|string|min:6|unique:users,phone',
                'device_id'   => 'nullable|string',
                'token_verify'=> 'nullable|string', // reçu depuis WhatsApp/SMS
                'user_type'   => 'nullable|in:customer,admin,merchant,driver',
            ]);

            // ✅ Transaction DB (rollback si problème)
            return DB::transaction(function () use ($validated) {


                // ✅ Création de l’utilisateur
                $user = User::create([
                    'name'      => $validated['name'],
                    'email'     => $validated['email'],
                    'phone'     => $validated['phone'],
                    'password'  => Hash::make($validated['password']),
                    'user_type' => $validated['user_type'] ?? 'customer',
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                // ✅ Création du profil en fonction du type
                switch ($user->user_type) {
                    case 'customer':
                        Customer::create([
                            'user_id' => $user->id,
                        ]);
                        break;

                    case 'merchant':
                        Merchant::create([
                            'user_id' => $user->id,
                        ]);
                        break;

                    case 'driver':
                        Driver::create([
                            'user_id'   => $user->id,
                            'device_id' => $validated['device_id'] ?? null,
                        ]);
                        break;
                }

                // ✅ Réponse succès
                return Helpers::success([
                    'user_id'      => $user->id,
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                ]);
            });
        } catch (ValidationException $e) {
            return Helpers::error($e->getMessage(), [
                'code' => 422,
                'details' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            return Helpers::error($e->getMessage(), [
                'code' => 500,
                'details' => $e->getTraceAsString(),
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
        $user->update(['fcm_token'=>$request->fcmId]);

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
    private function verifyToken($phone_number,$otp_code){


        $verification = PhoneVerification::where('phone_number', $phone_number)
            ->where('otp_code', $otp_code)
            ->first();

        if (!$verification) {
            return [
                'message'=>'Code incorrect',
                'status'=>false
            ];
        }

        if (now()->greaterThan($verification->expires_at)) {
            return [
                'message'=>'Code expiré',
                'status'=>false
            ];
        }

        $verification->update(['verified' => true]);

         return [
            'message'=>'Téléphone vérifié avec succès',
            'status'=>true
        ];
    }

    public function getCountries(Request $request){
        $countries=Country::all();
        return Helpers::success($countries);
    }
}
