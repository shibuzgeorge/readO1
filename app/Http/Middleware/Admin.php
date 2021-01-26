<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userRoles = $request->user()->role;
        if($userRoles->name !== 'Admin'){
            return response()->json(['error' => 'Unauthorized'], 403);

        }
        return $next($request);
    }
}
