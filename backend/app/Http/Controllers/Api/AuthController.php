<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // POST /api/v1/auth/register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Role is always forced to 'user' regardless of request body
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json($this->tokenResponse($token, $user), 201);
    }

    // POST /api/v1/auth/login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json($this->tokenResponse($token, auth('api')->user()), 200);
    }

    // GET /api/v1/auth/me (requires jwt.auth)
    public function me()
    {
        return response()->json(auth('api')->user(), 200);
    }

    // POST /api/v1/auth/logout (requires jwt.auth)
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out.'], 200);
    }

    // Build the standard token response payload
    private function tokenResponse(string $token, User $user): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
        ];
    }
}
