<?php

namespace App\Http\Requests\Practice;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompletePracticeSessionRequest extends FormRequest {
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
            'attempts' => ['required', 'array', 'min:1'],
            'attempts.*.word_id' => ['nullable', 'integer', Rule::exists('words', 'id')],
            'attempts.*.given_answer' => ['nullable', 'string', 'max:1000'],
            'attempts.*.correct_answer' => ['nullable', 'string', 'max:1000'],
            'attempts.*.is_correct' => ['required', 'boolean'],
            'attempts.*.response_time_ms' => ['nullable', 'integer', 'min:0'],
            'attempts.*.options' => ['nullable', 'array'],
        ];
    }
}
