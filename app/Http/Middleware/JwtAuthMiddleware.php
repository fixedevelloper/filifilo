<?php


namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JwtAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $matches[1];

        try {
            $publicKey = file_get_contents('public.pem'); // clé publique correspondante
            $credentials = JWT::decode($token, new Key($publicKey, 'RS256'));
            $userId = $credentials->sub;

            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            //logger($user);
            // Authentifie l’utilisateur Laravel pour cette requête
            Auth::login($user);
           } catch (\Exception $e) {
            logger($e);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
