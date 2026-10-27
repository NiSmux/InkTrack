<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    // Role hierarchy: higher number = more permissions
    private const HIERARCHY = [
        'user' => 1,
        'moderator' => 2,
        'admin' => 3,
    ];

    public function handle(Request $request, Closure $next, string $requiredRole)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $role = $payload->get('role') ?? 'user';
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userLevel = self::HIERARCHY[$role] ?? 0;
        $requiredLevel = self::HIERARCHY[$requiredRole] ?? 999;

        if ($userLevel < $requiredLevel) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
