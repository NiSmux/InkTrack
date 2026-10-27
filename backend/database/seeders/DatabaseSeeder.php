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
        // Users – password for all: password123
        // Order matters: Fantasy must be genre id 1, test@ must be user id 1
        $users = collect([
            ['Test User', 'test@example.com', 'user'],
            ['Moderator User', 'moderator@example.com', 'moderator'],
            ['Admin User', 'admin@example.com', 'admin'],
            ['Reader One', 'reader1@example.com', 'user'],
            ['Reader Two', 'reader2@example.com', 'user'],
        ])->map(fn($u) => User::firstOrCreate(
                ['email' => $u[1]],
                ['name' => $u[0], 'password' => 'password123', 'role' => $u[2]]
            ));

        // Genres – Fantasy must be id 1
        $genres = collect(['Fantasy', 'Detective', 'Romance', 'History'])
            ->mapWithKeys(fn($name) => [$name => Genre::firstOrCreate(['name' => $name])]);

        // Books – The Hobbit must be id 1
        $books = collect([
            ['Fantasy', 'The Hobbit', 'J. R. R. Tolkien', 'A hobbit goes on an unexpected journey.'],
            ['Fantasy', 'Harry Potter and the Philosopher\'s Stone', 'J. K. Rowling', 'A boy discovers he is a wizard.'],
            ['Detective', 'The Hound of the Baskervilles', 'Arthur Conan Doyle', 'Sherlock Holmes investigates a legendary hound.'],
            ['Detective', 'Murder on the Orient Express', 'Agatha Christie', 'Poirot solves a murder on a snowbound train.'],
            ['Romance', 'Pride and Prejudice', 'Jane Austen', 'Elizabeth Bennet and Mr. Darcy navigate society.'],
            ['History', 'The Diary of a Young Girl', 'Anne Frank', 'Diary written during the Second World War.'],
        ])->map(fn($b) => Book::firstOrCreate(
                ['title' => $b[1], 'author' => $b[2]],
                ['genre_id' => $genres[$b[0]]->id, 'description' => $b[3], 'cover_image' => null]
            ));

        // Reviews – one per user per book, ratings in 0.5 steps
        $reviewData = [
            [0, 0, 5.0, 'Puiki knyga, skaičiau kelis kartus.'],
            [1, 1, 4.5, 'Labai įtraukianti.'],
            [2, 2, 4.0, 'Gera pradžia serijai.'],
            [3, 3, 4.5, 'Netikėta pabaiga.'],
            [4, 4, 3.5, 'Klasika, bet vietomis lėta.'],
            [0, 5, 5.0, 'Stipri ir jautri knyga.'],
        ];

        foreach ($reviewData as [$u, $b, $rating, $comment]) {
            Review::firstOrCreate(
                ['user_id' => $users[$u]->id, 'book_id' => $books[$b]->id],
                ['rating' => $rating, 'comment' => $comment]
            );
        }
    }
}