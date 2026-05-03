<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SentenceGenerator;
use App\Enums\AnswerForm;
use App\Enums\ExerciseStructure;
use App\Enums\ExerciseType;
use App\Enums\QuestionForm;
use App\Models\PracticeAttempt;
use App\Models\PracticeSession;
use App\Models\PracticeSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PracticeSessionController extends Controller {
    public function __construct() {
        $this->authorizeResource(PracticeSession::class, 'practiceSession');
    }

    public function store(Request $request): RedirectResponse {
        $validated = $request->validate([
            'practice_set_id' => ['required', 'integer', Rule::exists('practice_sets', 'id')],
            'exercise_structure' => ['required', Rule::enum(ExerciseStructure::class)],
            'exercise_type' => ['required', Rule::enum(ExerciseType::class)],
            'question_form' => ['required', Rule::enum(QuestionForm::class)],
            'answer_form' => ['required', Rule::enum(AnswerForm::class)],
        ]);

        $set = PracticeSet::where('id', $validated['practice_set_id'])
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        $session = PracticeSession::create([
            'user_id' => Auth::user()->id,
            'practice_set_id' => $set->id,
            'exercise_structure' => $validated['exercise_structure'],
            'exercise_type' => $validated['exercise_type'],
            'question_form' => $validated['question_form'],
            'answer_form' => $validated['answer_form'],
        ]);

        return redirect()->route('practice.sessions.show', $session);
    }

    public function show(PracticeSession $practiceSession): Response {
        $words = [];
        $results = null;

        if ($practiceSession->completed_at) {
            $results = $practiceSession->attempts()
                ->with('word')
                ->orderBy('id')
                ->get();
        } else {
            $words = match ($practiceSession->exercise_structure) {
                ExerciseStructure::Word => (function () use ($practiceSession) {
                    return $practiceSession->practiceSet?->words()
                        ->select('words.id', 'text', 'pinyin', 'translation')
                        ->get()
                        ->shuffle()
                        ->values() ?? collect();
                })(),
                ExerciseStructure::Sentence => (function () {
                    $sentences = (new SentenceGenerator())->prompt('go')['sentences'];

                    return collect($sentences)
                        ->map(fn ($sentence, $idx) => [
                            'text' => $sentence['chinese'],
                            'pinyin' => $sentence['pinyin'],
                            'translation' => $sentence['english'],
                        ]);
                })()
            };
        }

        return Inertia::render('practice/session', [
            'session' => $practiceSession,
            'words' => $words,
            'results' => $results,
        ]);
    }

    public function complete(Request $request, PracticeSession $practiceSession): RedirectResponse {
        $this->authorize('complete', $practiceSession);

        $validated = $request->validate([
            'attempts' => ['required', 'array', 'min:1'],
            'attempts.*.word_id' => ['nullable', 'integer', Rule::exists('words', 'id')],
            'attempts.*.given_answer' => ['nullable', 'string', 'max:1000'],
            'attempts.*.correct_answer' => ['nullable', 'string', 'max:1000'],
            'attempts.*.is_correct' => ['required', 'boolean'],
            'attempts.*.response_time_ms' => ['nullable', 'integer', 'min:0'],
            'attempts.*.options' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($practiceSession, $validated): void {
            foreach ($validated['attempts'] as $data) {
                PracticeAttempt::create([
                    'practice_session_id' => $practiceSession->id,
                    'word_id' => $data['word_id'],
                    'given_answer' => $data['given_answer'] ?? null,
                    'correct_answer' => $data['correct_answer'],
                    'is_correct' => $data['is_correct'],
                    'response_time_ms' => $data['response_time_ms'] ?? null,
                    'options' => $data['options'] ?? null,
                ]);
            }

            $practiceSession->update(['completed_at' => now()]);
        });

        return redirect()->route('practice.sessions.show', $practiceSession);
    }
}
