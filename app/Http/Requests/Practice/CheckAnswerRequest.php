<?php

namespace App\Http\Requests\Practice;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckAnswerRequest extends FormRequest {
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
            'given_answer' => ['required', 'string', 'max:1000'],
            'correct_answer' => ['required', 'string', 'max:1000'],
        ];
    }
}
