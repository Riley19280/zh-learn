<?php

namespace App\Http\Requests\Practice;

use App\Enums\AnswerForm;
use App\Enums\ExerciseStructure;
use App\Enums\ExerciseType;
use App\Enums\QuestionForm;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePracticeSessionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'practice_set_id' => ['required', 'integer', Rule::exists('practice_sets', 'id')->where('user_id', $this->user()->id)],
            'exercise_structure' => ['required', Rule::enum(ExerciseStructure::class)],
            'exercise_type' => ['required', Rule::enum(ExerciseType::class)],
            'question_form' => ['required', Rule::enum(QuestionForm::class)],
            'answer_form' => ['required', Rule::enum(AnswerForm::class)],
        ];
    }
}
