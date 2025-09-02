<?php


namespace App\Http\Controllers\API\V2\Auth;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function requestReset(Request $request) {

        $request->validate([
            'email' => 'required|exists:users,email',
        ]);

        // Générer un token (OTP ou hash)
        $token = rand(1000, 9999);

        // Sauvegarder en DB
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now(),
            ]
        );

        // TODO : Envoyer le code par SMS (via Twilio, Nexmo ou autre service)
        // Pour le test : on retourne le token
        //return Helpers::success('Code de réinitialisation envoyé par SMS');
        return Helpers::success([
            'message'=>'Code de réinitialisation envoyé par SMS',
            'token'=>$token
        ]);
    }
    public function reset(Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return Helpers::error($validator->errors()->first());
        }
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return Helpers::error( 'Code invalide', 400);
        }

        // Vérifier expiration (optionnel, ex : 15 minutes)
        if (Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'Code expiré'], 400);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return Helpers::success('Mot de passe réinitialisé avec succès');
    }
    public function changePassword(Request $request)
    {

        $request->validate([
            'new_password' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = Auth::user();

        if (!$user) {
            return Helpers::error('$customer est requis', 400);
        }
        if (!Auth::attempt(['phone' => $user->phone, 'password' => $request->password])) {
            return Helpers::error('Invalid credentials');

        }
        $user->update([
            'password' => Hash::make($request->new_password)

        ]);

        return Helpers::success([
            'first_name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'balance' => 0.0,
            'date_birth' => date('Y-m-d')
        ]);
    }
    public function sendCodeVerify(Request $request)
    {

        $request->validate([
            'phone' => 'required|string',
        ]);

        return Helpers::success('verify ok');
    }
    public function verifyCode(Request $request)
    {

        $request->validate([
            'new_password' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = Auth::user();

        if (!$user) {
            return Helpers::error('$customer est requis', 400);
        }
        if (!Auth::attempt(['phone' => $user->phone, 'password' => $request->password])) {
            return Helpers::error('Invalid credentials');

        }
        $user->update([
            'password' => Hash::make($request->new_password)

        ]);

        return Helpers::success([
            'first_name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'balance' => 0.0,
            'date_birth' => date('Y-m-d')
        ]);
    }
}
