<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // 1. GET /api/books
    public function index()
    {
        $books = Book::with('genre')->get();

        return response()->json($books, 200);
    }

    // 2. GET /api/books/{id}
    public function show($id)
    {
        $book = Book::with('genre')->find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        return response()->json($book, 200);
    }

    // 3. POST /api/books
    public function store(Request $request)
    {
        $validated = $request->validate([
            'genre_id' => ['required', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
        ]);

        $book = Book::create($validated);

        return response()->json(
            $book->load('genre'),
            201
        );
    }

    // 4. PUT /api/books/{id}
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $validated = $request->validate([
            'genre_id' => ['required', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
        ]);

        $book->update($validated);

        return response()->json(
            $book->load('genre'),
            200
        );
    }

    // 5. DELETE /api/books/{id}
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ], 200);
    }
}