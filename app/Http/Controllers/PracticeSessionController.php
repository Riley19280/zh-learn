<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AnswerAnalzyer;
use App\Ai\Agents\SentenceGenerator;
use App\Enums\AnswerForm;
use App\Enums\ExerciseStructure;
use App\Enums\ExerciseType;
use App\Http\Requests\Practice\CheckAnswerRequest;
use App\Http\Requests\Practice\CompletePracticeSessionRequest;
use App\Http\Requests\Practice\StorePracticeSessionRequest;
use App\Models\PracticeAttempt;
use App\Models\PracticeSession;
use App\Models\PracticeSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Normalizer;

class PracticeSessionController extends Controller {
    public function __construct() {
        $this->authorizeResource(PracticeSession::class, 'practiceSession');
    }

    public function store(StorePracticeSessionRequest $request): RedirectResponse {
        $validated = $request->validated();

        $set = PracticeSet::findOrFail($validated['practice_set_id']);

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

    public function complete(CompletePracticeSessionRequest $request, PracticeSession $practiceSession): RedirectResponse {
        $this->authorize('complete', $practiceSession);

        $validated = $request->validated();

        $isSentenceTyping = $practiceSession->exercise_structure === ExerciseStructure::Sentence
            && $practiceSession->exercise_type === ExerciseType::Typing;

        DB::transaction(function () use ($practiceSession, $validated, $isSentenceTyping): void {
            foreach ($validated['attempts'] as $data) {
                $isCorrect = $isSentenceTyping
                    ? $this->evaluateAnswer($data['given_answer'] ?? '', $data['correct_answer'] ?? '', $practiceSession->answer_form)
                    : $data['is_correct'];

                PracticeAttempt::create([
                    'practice_session_id' => $practiceSession->id,
                    'word_id' => $data['word_id'],
                    'given_answer' => $data['given_answer'] ?? null,
                    'correct_answer' => $data['correct_answer'],
                    'is_correct' => $isCorrect,
                    'response_time_ms' => $data['response_time_ms'] ?? null,
                    'options' => $data['options'] ?? null,
                ]);
            }

            $practiceSession->update(['completed_at' => now()]);
        });

        return redirect()->route('practice.sessions.show', $practiceSession);
    }

    public function checkAnswer(CheckAnswerRequest $request, PracticeSession $practiceSession): JsonResponse {
        $this->authorize('view', $practiceSession);

        $validated = $request->validated();

        $isCorrect = $this->evaluateAnswer(
            $validated['given_answer'],
            $validated['correct_answer'],
            $practiceSession->answer_form,
        );

        $feedback = null;
        $isTechnicallyCorrect = null;

        if (!$isCorrect) {
            $result = (new AnswerAnalzyer(
                givenAnswer: $validated['given_answer'],
                correctAnswer: $validated['correct_answer'],
                answerForm: $practiceSession->answer_form,
            ))->prompt('go');
            $feedback = $result['feedback'];
            $isTechnicallyCorrect = $result['is_technically_correct'];
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'is_technically_correct' => $isTechnicallyCorrect,
            'feedback' => $feedback,
        ]);
    }

    private function evaluateAnswer(string $given, string $correct, AnswerForm $answerForm): bool {
        $g = $this->normalize($given);
        $c = $this->normalize($correct);

        if ($g === $c) {
            return true;
        }

        if ($answerForm === AnswerForm::Pinyin) {
            return $this->stripDiacritics($g) === $this->stripDiacritics($c);
        }

        return false;
    }

    private function normalize(string $s): string {
        $s = mb_strtolower(trim($s));

        // Strip punctuation (Unicode-aware)
        return preg_replace('/[\p{P}\p{S}]/u', '', $s) ?? $s;
    }

    private function stripDiacritics(string $s): string {
        $normalized = Normalizer::normalize($s, Normalizer::NFD);

        return preg_replace('/[\x{0300}-\x{036F}]/u', '', $normalized) ?? $s;
    }
}
