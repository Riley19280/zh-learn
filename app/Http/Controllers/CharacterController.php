<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Models\Word;
use Inertia\Inertia;
use Inertia\Response;

class CharacterController extends Controller {
    public function show(Character $character): Response {
        /** @var User $user */
        $user = auth()->user();

        $words = Word::select('words.*')
            ->join('word_characters', fn ($j) => $j->on('words.id', '=', 'word_characters.word_id')
                ->where('word_characters.character_id', $character->id))
            ->orderBy('words.text')
            ->get()
            ->keyBy('id');

        $userWords = $user->words()->whereIn('words.id', $words->pluck('id'))->get()->keyBy('id');

        // Map so that we have the pivot is_available value
        foreach ($userWords as $userWord) {
            $words[$userWord->id] = $userWord;
        }

        return Inertia::render('characters/show', [
            'character' => $character->character,
            'words' => $words->values(),
        ]);
    }
}
