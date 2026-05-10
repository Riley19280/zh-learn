<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use App\Models\UserSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller {
    public function index(): Response {
        /** @var User $user */
        $user = auth()->user();

        $userSectionMap = UserSection::where('user_id', $user->id)
            ->get()
            ->keyBy('section_id');

        $sections = Section::query()
            ->withCount('words')
            ->orderBy('section_number')
            ->orderBy('unit_number')
            ->get();

        return Inertia::render('sections/index', [
            'sections' => $sections,
        ]);
    }

    public function show(Section $section): Response {
        /** @var User $user */
        $user = auth()->user();

        $words = $section->words()
            ->select('words.*', 'user_word.is_available')
            ->leftJoin('user_word', fn ($j) => $j->on('words.id', '=', 'user_word.word_id')
                ->where('user_word.user_id', $user->id))
            ->orderBy('words.text')
            ->get()
            ->keyBy('id');

        $userWords = $user->words()->whereIn('words.id', $words->pluck('id'))->get()->keyBy('id');

        // Map so that we have the pivot is_available value
        foreach ($userWords as $userWord) {
            $words[$userWord->id] = $userWord;
        }

        return Inertia::render('sections/show', [
            'section' => $section,
            'words' => $words->values(),
        ]);
    }

    public function update(Request $request, Section $section): RedirectResponse {
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'is_unlocked' => ['required', 'boolean'],
        ]);

        DB::table('user_section')->updateOrInsert(
            ['user_id' => $user->id, 'section_id' => $section->id],
            ['is_unlocked' => $validated['is_unlocked'], 'updated_at' => now(), 'created_at' => now()]
        );

        $wordIds = $section->words()->pluck('words.id');

        if ($validated['is_unlocked']) {
            $user->words()->syncWithoutDetaching(
                $wordIds->mapWithKeys(fn ($id) => [$id => ['is_available' => true]])->all()
            );
        } else {
            $user->words()->detach($wordIds);
        }

        return back();
    }
}
