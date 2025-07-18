<?php

namespace App\Http\Middleware;

use App\Helpers\api\Helpers;
use App\Models\User;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class EnsureTokenValid
{
    public function handle(Request $request, Closure $next)
    {
        $privateKey = file_get_contents('private.pem');
        $publicKey = file_get_contents('public.pem');
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return Helpers::unauthorized('','Token not provided');

        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        try {
            // Décodage non vérifié pour extraire customer_id
            $payload = json_decode(base64_decode(explode('.', $token)[1]), true);
            $customerId = $payload['sub'] ?? null;

            if (!$customerId) {
                throw new Exception('Invalid token payload');
            }

            $customer = User::find($customerId);

            if (! $customer || ! $publicKey) {
                throw new Exception('Customer not found or missing public key');
            }
            // Vérification du token avec la clé publique
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            // Injecter le customer dans la requête
            $request->merge(['customer' => $customer]);

        } catch (Exception $e) {
            return Helpers::unauthorized('Invalid token: ' . $e->getMessage(),'Invalid token: ' . $e->getMessage());

        }

        return $next($request);
    }
}
