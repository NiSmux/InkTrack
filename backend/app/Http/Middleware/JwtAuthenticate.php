<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token not provided.'], 401);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 401);
        }

        return $next($request);
    }
}
