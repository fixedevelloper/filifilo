<?php


namespace App\Http\Middleware;

use App\Helpers\api\Helpers;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return Helpers::unauthorized($e->getCode(),'Token expiré');
        } catch (TokenInvalidException $e) {
            return Helpers::unauthorized($e->getCode(),'Token invalide');

        } catch (JWTException $e) {
            return Helpers::unauthorized($e->getCode(),'Token absent ou invalide');
        }

        return $next($request);
    }
}
