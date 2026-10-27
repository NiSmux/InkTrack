<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // GET /api/v1/users  (admin only)
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();

        return response()->json($users, 200);
    }

    // GET /api/v1/users/{id}  (public profile – no email/password)
    public function show($id)
    {
        $user = User::with(['reviews.book:id,title,author'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'reviews' => $user->reviews->map(fn($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'book' => $r->book ? [
                    'id' => $r->book->id,
                    'title' => $r->book->title,
                    'author' => $r->book->author,
                ] : null,
            ]),
        ], 200);
    }

    // PUT /api/v1/users/{id}  (owner only)
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $authUser = auth('api')->user();

        if ($authUser->id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        // Ignore any role change attempt
        unset($validated['role']);

        $user->update($validated);

        return response()->json($user->fresh(), 200);
    }

    // PUT /api/v1/users/{id}/role  (admin only)
    public function updateRole(Request $request, $id)
    {
        $authUser = auth('api')->user();

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($authUser->id === $user->id) {
            return response()->json(['message' => 'Forbidden. Cannot change your own role.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'moderator', 'admin'])],
        ]);

        $user->update(['role' => $validated['role']]);

        return response()->json($user->fresh(), 200);
    }

    // DELETE /api/v1/users/{id}  (admin only)
    public function destroy($id)
    {
        $authUser = auth('api')->user();

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($authUser->id === $user->id) {
            return response()->json(['message' => 'Forbidden. Cannot delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
