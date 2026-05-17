<?php

namespace App\Http\Controllers;

use App\Models\PracticeSet;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PracticeSetController extends Controller {
    public function __construct() {
        $this->authorizeResource(PracticeSet::class, 'practiceSet');
    }

    public function create(): Response {
        /** @var User $user */
        $user = auth()->user();

        $sections = Section::query()
            ->withCount('words')
            ->orderBy('section_number')
            ->orderBy('unit_number')
            ->get()
            ->keyBy('id')
            ->merge($user->sections->keyBy('id'))
            ->load('words');

        return Inertia::render('practice/sets/form', [
            'sections' => $sections,
        ]);
    }

    public function store(Request $request): RedirectResponse {
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'word_ids' => ['required', 'array', 'min:1'],
            'word_ids.*' => ['integer', 'exists:words,id'],
        ]);

        $set = PracticeSet::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
        ]);

        $set->words()->sync($validated['word_ids']);

        return redirect()->route('practice.index');
    }

    public function edit(PracticeSet $practiceSet): Response {
        return Inertia::render('practice/sets/form', [
            'practiceSet' => [
                'id' => $practiceSet->id,
                'name' => $practiceSet->name,
                'wordIds' => $practiceSet->words()->pluck('words.id'),
            ],
            'sections' => $this->sectionsWithWords(),
        ]);
    }

    public function update(Request $request, PracticeSet $practiceSet): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'word_ids' => ['required', 'array', 'min:1'],
            'word_ids.*' => ['integer', 'exists:words,id'],
        ]);

        $practiceSet->update(['name' => $validated['name']]);
        $practiceSet->words()->sync($validated['word_ids']);

        return redirect()->route('practice.index');
    }

    public function destroy(PracticeSet $practiceSet): RedirectResponse {
        $practiceSet->delete();

        return redirect()->route('practice.index');
    }
}
