<?php

namespace App\Http\Middleware;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Log;

class Authenticate
{
    public function handle($request, Closure $next)
    {
        try {
            Log::info('JWT Middleware: Checking token');
            $user = JWTAuth::parseToken()->authenticate();
            Log::info('JWT Middleware: Token valid');
        } catch (Exception $e) {
            Log::error('JWT Middleware Exception: ' . $e->getMessage());
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => 'Token is Invalid'], 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => 'Token is Expired'], 401);
            } else {
                return response()->json(['status' => 'Authorization Token not found'], 401);
            }
        }

        return $next($request);
    }
}