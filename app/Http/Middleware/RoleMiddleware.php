<?php

namespace App\Http\Middleware;

use Closure;
use HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        //$user = $request->user();
        $user = Auth::user();
        logger('user-----------'.$user);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Si le rôle de l'utilisateur n'est pas dans la liste autorisée
        if (!in_array($user->user_type, $roles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        try {
            return $next($request);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Resource not found',
                'exception' => 'NotFoundHttpException',
            ], 404);
        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'status' => $e->getStatusCode(),
                'message' => $e->getMessage() ?: 'HTTP Error',
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Server Error',
            ], 500);
        }
       // return $next($request);
    }
}
