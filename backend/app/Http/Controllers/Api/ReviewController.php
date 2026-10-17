<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    // 1. GET /api/reviews
    public function index()
    {
        $reviews = Review::with(['user', 'book'])->get();

        return response()->json($reviews, 200);
    }

    // 2. GET /api/reviews/{id}
    public function show($id)
    {
        $review = Review::with(['user', 'book'])->find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json($review, 200);
    }

    // 3. POST /api/reviews
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => [
                'required',
                'exists:books,id',
                Rule::unique('reviews')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                }),
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

        $review = Review::create($validated);

        return response()->json(
            $review->load(['user', 'book']),
            201
        );
    }

    // 4. PUT /api/reviews/{id}
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found'
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => [
                'required',
                'exists:books,id',
                Rule::unique('reviews')
                    ->where(function ($query) use ($request) {
                        return $query->where('user_id', $request->user_id);
                    })
                    ->ignore($review->id),
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

        $review->update($validated);

        return response()->json(
            $review->load(['user', 'book']),
            200
        );
    }

    // 5. DELETE /api/reviews/{id}
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ], 200);
    }
}