<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\UserGroup;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && UserGroup::where('user_id', Auth::id())->where('group_id', '2')->exist()) {
            return $next($request);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorised'
            ], 401);
        }
    }
}
