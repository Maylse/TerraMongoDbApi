<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
     /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and is an admin
        if ($request->user() && $request->user()->is_admin) {
            return $next($request);
        }

        // If the user is not an admin, return a 403 response
        return response()->json(['message' => 'Unauthorized'], 403);
    }

}
