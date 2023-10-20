<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    /**
     * Use user_id instead of email
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    // public function handle($request, Closure $next)
    // {
    //     $credentials = $request->only('user_id', 'password');

    //     if ($token = JWTAuth::attempt($credentials)) {
    //         // Token generation successful, proceed with the authenticated user
    //         return $next($request);
    //     }

    //     // Token generation failed, return an unauthorized response
    //     return response()->json(['message' => 'Unauthorized'], 401);
    // }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
