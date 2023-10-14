<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {

        if(auth()->user()) {
            //SuperAdmin
            if($role == 1) {
                if (auth()->user()->role_id == $role) {
                    // dd('dd');
                    return $next($request);
                } else {
                    return response()->json([
                        'message' => 'User unauthorized',
                    ], 401); 
                }
            }
            //TeamLeader
            else if($role == 2) { 
                if (auth()->user()->role_id <= $role) {
                    dd('dd');
                    return $next($request);
                } else {
                    return response()->json([
                        'message' => 'User unauthorized',
                    ], 401); 
                }
            }
            //User 
            else {
                return $next($request);
            }
        } else {
            
            return response()->json([
                'message' => 'User unauthorized',
            ], 401); 
        }



    }
}
