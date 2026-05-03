<?php

namespace App\Http\Controllers;

use App\Enums\AnswerForm;
use App\Enums\ExerciseStructure;
use App\Enums\ExerciseType;
use App\Enums\QuestionForm;
use App\Models\PracticeSession;
use App\Models\PracticeSet;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
{
    public function index(): Response
    {
        $sets = PracticeSet::query()
            ->where('user_id', Auth::user()->id)
            ->withCount('words')
            ->orderBy('name')
            ->get();

        $sessions = PracticeSession::query()
            ->where('user_id', Auth::user()->id)
            ->whereNotNull('completed_at')
            ->with('practiceSet')
            ->withCount([
                'attempts',
                'correctAttempts',
            ])
            ->orderByDesc('completed_at')
            ->limit(25)
            ->get();

        return Inertia::render('practice/index', [
            'sets' => $sets,
            'sessions' => $sessions,
            'exercise_structures' => array_map(fn (ExerciseStructure $e) => ['value' => $e->value, 'label' => $e->label()], ExerciseStructure::cases()),
            'exercise_types' => array_map(fn (ExerciseType $e) => ['value' => $e->value, 'label' => $e->label()], ExerciseType::cases()),
            'question_forms' => array_map(fn (QuestionForm $q) => ['value' => $q->value, 'label' => $q->label()], QuestionForm::cases()),
            'answer_forms' => array_map(fn (AnswerForm $a) => ['value' => $a->value, 'label' => $a->label()], AnswerForm::cases()),
        ]);
    }
}
