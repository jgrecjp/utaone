<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! hash_equals('Bearer '.config('utaone.admin_api_token'), (string) $request->header('Authorization', ''))) {
            return response()->json(['detail' => 'Invalid admin token'], 401);
        }

        return $next($request);
    }
}
