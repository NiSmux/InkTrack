<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    // GET /api/v1/reviews  (public; optional filters: book_id, user_id)
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name', 'book']);

        if ($bookId = $request->query('book_id')) {
            $query->where('book_id', $bookId);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        return response()->json($query->get(), 200);
    }

    // GET /api/v1/reviews/{id}  (public)
    public function show($id)
    {
        $review = Review::with(['user:id,name', 'book'])->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json($review, 200);
    }

    // POST /api/v1/reviews  (any authenticated user)
    public function store(Request $request)
    {
        $authUser = auth('api')->user();

        $validated = $request->validate([
            'book_id' => [
                'required',
                'exists:books,id',
                Rule::unique('reviews')->where(fn($q) => $q->where('user_id', $authUser->id)),
            ],
            'rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5',
                function ($attribute, $value, $fail) {
                    if (fmod((float) $value, 0.5) !== 0.0) {
                        $fail('The rating must be in 0.5 increments.');
                    }
                },
            ],
            'comment' => ['nullable', 'string'],
        ]);

        // user_id always comes from token, not request
        $review = Review::create([
            'user_id' => $authUser->id,
            'book_id' => $validated['book_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review->load(['user:id,name', 'book']), 201);
    }

    // PUT /api/v1/reviews/{id}  (owner only)
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $authUser = auth('api')->user();

        if ($authUser->id !== $review->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5',
                function ($attribute, $value, $fail) {
                    if (fmod((float) $value, 0.5) !== 0.0) {
                        $fail('The rating must be in 0.5 increments.');
                    }
                },
            ],
            'comment' => ['nullable', 'string'],
        ]);

        $review->update($validated);

        return response()->json($review->load(['user:id,name', 'book']), 200);
    }

    // DELETE /api/v1/reviews/{id}  (owner, moderator, or admin)
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $authUser = auth('api')->user();

        // Read role from JWT payload (never from DB)
        $payload = JWTAuth::parseToken()->getPayload();
        $role = $payload->get('role') ?? 'user';

        $isOwner = $authUser->id === $review->user_id;
        $isMod = in_array($role, ['moderator', 'admin']);

        if (!$isOwner && !$isMod) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }
}