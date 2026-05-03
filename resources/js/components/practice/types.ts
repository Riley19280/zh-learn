export interface LocalAttempt {
    word_id: number;
    given_answer: string | null;
    correct_answer: string;
    is_correct: boolean;
    response_time_ms: number;
    options: string[] | null;
}
