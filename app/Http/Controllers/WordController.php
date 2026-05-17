<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Models\Word;
use Inertia\Inertia;
use Inertia\Response;

class WordController extends Controller {
    public function index(): Response {
        /** @var User $user */
        $user = auth()->user();

        $words = Word::query()
            ->orderBy('text')
            ->get()
            ->keyBy('id')
            ->merge($user->words->keyBy('id'));

        $notes = Note::query()
            ->where('user_id', $user->id)
            ->where('notable_type', Word::class)
            ->orderBy('created_at')
            ->get();

        return Inertia::render('words/index', [
            'words' => $words,
            'notes' => $notes,
        ]);
    }
}
