<?php

namespace App\Ai\Agents;

use App\Enums\AnswerForm;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class AnswerAnalzyer implements Agent, HasStructuredOutput {
    use Promptable;

    public function __construct(
        public readonly string $givenAnswer,
        public readonly string $correctAnswer,
        public readonly AnswerForm $answerForm,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string {
        return view('ai.AnswerAnalzyerPrompt', [
            'correctAnswer' => $this->correctAnswer,
            'givenAnswer' => $this->givenAnswer,
            'answerForm' => $this->answerForm->label(),
        ])->render();
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array {
        return [
            'is_technically_correct' => $schema->boolean()->required(),
            'feedback' => $schema->string()->required(),
        ];
    }
}
