<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    // 1. GET /api/genres
    public function index()
    {
        $genres = Genre::all();

        return response()->json($genres, 200);
    }

    // 2. GET /api/genres/{id}
    public function show($id)
    {
        $genre = Genre::with('books')->find($id);

        if (!$genre) {
            return response()->json([
                'message' => 'Genre not found'
            ], 404);
        }

        return response()->json($genre, 200);
    }

    // 3. POST /api/genres
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:genres,name'],
        ]);

        $genre = Genre::create($validated);

        return response()->json($genre, 201);
    }

    // 4. PUT /api/genres/{id}
    public function update(Request $request, $id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json([
                'message' => 'Genre not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:genres,name,' . $genre->id,
            ],
        ]);

        $genre->update($validated);

        return response()->json($genre, 200);
    }

    // 5. DELETE /api/genres/{id}
    public function destroy($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json([
                'message' => 'Genre not found'
            ], 404);
        }

        $genre->delete();

        return response()->json([
            'message' => 'Genre deleted successfully'
        ], 200);
    }
}