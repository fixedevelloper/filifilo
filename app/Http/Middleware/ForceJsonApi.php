<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonApi
{
    public function handle(Request $request, Closure $next)
    {
        // Forcer le format JSON pour toutes les routes api/*
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
