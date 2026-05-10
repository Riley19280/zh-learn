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

        $words = $user->words()
            ->orderBy('text')
            ->get();

        $note = Note::query()
            ->where('user_id', $user->id)
            ->where('notable_type', Word::class)
            ->orderBy('created_at')
            ->first();

        return Inertia::render('words/index', [
            'words' => $words,
            'note' => $note,
        ]);
    }
}
