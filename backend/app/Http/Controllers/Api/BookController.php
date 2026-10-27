<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // GET /api/v1/books  (public; optional filters: search, title, author, genre_id)
    public function index(Request $request)
    {
        $query = Book::with('genre')->orderBy('id');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($title = $request->query('title')) {
            $query->where('title', 'like', "%{$title}%");
        }

        if ($author = $request->query('author')) {
            $query->where('author', 'like', "%{$author}%");
        }

        if ($genreId = $request->query('genre_id')) {
            $query->where('genre_id', $genreId);
        }

        return response()->json($query->get(), 200);
    }

    // GET /api/v1/books/{id}  (public; includes reviews with author id+name only)
    public function show($id)
    {
        $book = Book::with(['genre', 'reviews.user:id,name'])->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json($book, 200);
    }

    // POST /api/v1/books  (admin only)
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

        return response()->json($book->load('genre'), 201);
    }

    // PUT /api/v1/books/{id}  (admin only)
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $validated = $request->validate([
            'genre_id' => ['required', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
        ]);

        $book->update($validated);

        return response()->json($book->load('genre'), 200);
    }

    // DELETE /api/v1/books/{id}  (admin only)
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}