<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Vartotojai (pirmas turi būti id = 1 – jį naudoja Postman kolekcija)
        $users = collect([
            ['Test User', 'test@example.com', 'user'],
            ['Admin User', 'admin@example.com', 'admin'],
            ['Jonas Jonaitis', 'jonas@example.com', 'user'],
        ])->map(fn ($u) => User::firstOrCreate(
            ['email' => $u[1]],
            ['name' => $u[0], 'password' => 'password123', 'role' => $u[2]]
        ));

        // Žanrai (Fantasy turi būti id = 1)
        $genres = collect(['Fantasy', 'Detective', 'Romance', 'History'])
            ->mapWithKeys(fn ($name) => [$name => Genre::firstOrCreate(['name' => $name])]);

        // Knygos (The Hobbit turi būti id = 1)
        $books = collect([
            ['Fantasy', 'The Hobbit', 'J. R. R. Tolkien', 'A hobbit goes on an unexpected journey.'],
            ['Fantasy', 'Harry Potter and the Philosopher\'s Stone', 'J. K. Rowling', 'A boy discovers he is a wizard.'],
            ['Detective', 'The Hound of the Baskervilles', 'Arthur Conan Doyle', 'Sherlock Holmes investigates a legendary hound.'],
            ['Detective', 'Murder on the Orient Express', 'Agatha Christie', 'Poirot solves a murder on a snowbound train.'],
            ['Romance', 'Pride and Prejudice', 'Jane Austen', 'Elizabeth Bennet and Mr. Darcy.'],
            ['History', 'The Diary of a Young Girl', 'Anne Frank', 'Diary written during the Second World War.'],
        ])->map(fn ($b) => Book::firstOrCreate(
            ['title' => $b[1], 'author' => $b[2]],
            ['genre_id' => $genres[$b[0]]->id, 'description' => $b[3], 'cover_image' => null]
        ));

        // Atsiliepimai (reitingas žingsniu 0.5; vienas vartotojas – viena knyga)
        $reviews = [
            [0, 1, 5.0, 'Puiki knyga, skaičiau kelis kartus.'],
            [0, 2, 4.5, 'Labai įtraukianti.'],
            [1, 0, 4.0, 'Gera pradžia serijai.'],
            [1, 3, 4.5, 'Netikėta pabaiga.'],
            [2, 4, 3.5, 'Klasika, bet vietomis lėta.'],
            [2, 5, 5.0, 'Stipri ir jautri knyga.'],
        ];

        foreach ($reviews as [$u, $b, $rating, $comment]) {
            Review::firstOrCreate(
                ['user_id' => $users[$u]->id, 'book_id' => $books[$b]->id],
                ['rating' => $rating, 'comment' => $comment]
            );
        }
    }
}