import { Input } from '@/components/ui/input';
import { checkAnswer as checkAnswerRoute } from '@/routes/practice/sessions/index';
import  { PracticeAttempt, PracticeSession,
    UnsavedPracticeAttempt, Word } from '@/types';
import { useHttp } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { checkAnswerClient, getCorrectAnswer } from './helpers';
import { QuestionDisplay } from './QuestionDisplay';

export function TypingExercise({
    word,
    session,
    onAnswer,
}: {
    word: Word;
    session: PracticeSession;
    onAnswer: (attempt: UnsavedPracticeAttempt) => void;
}) {
    const [submitted, setSubmitted] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);
    const startTime = useRef(Date.now());

    const isSentenceTyping =
        session.exercise_structure === 'sentence' &&
        session.exercise_type === 'typing';

    const correctAnswer = getCorrectAnswer(word, session.answer_form);

    const {
        data,
        setData,
        post: checkAnswerServer,
        processing: checking,
    } = useHttp<
        { given_answer: string; correct_answer: string },
        {
            is_correct: boolean;
            is_technically_correct: boolean | null;
            feedback: string | null;
        }
    >({
        given_answer: '',
        correct_answer: correctAnswer,
    });

    useEffect(() => {
        inputRef.current?.focus();
    }, []);

    function check() {
        if (submitted || !data.given_answer.trim()) {
            return;
        }

        setSubmitted(true);
        const responseTime = Date.now() - startTime.current;

        const isCorrect = checkAnswerClient(
            data.given_answer,
            correctAnswer,
            session.answer_form,
        );

        if (isSentenceTyping) {
            checkAnswerServer(checkAnswerRoute.url(session.id), {
                onSuccess: (response) => {
                    onAnswer({
                        word_id: word.id,
                        given_answer: data.given_answer,
                        correct_answer: correctAnswer,
                        is_correct: (response.is_correct || response.is_technically_correct) ?? false,
                        response_time_ms: responseTime,
                        options: null,
                        feedback: response.feedback,
                    });
                },
                onError: () => {
                    setSubmitted(false);
                },
            });
        } else {
            onAnswer({
                word_id: word.id,
                given_answer: data.given_answer,
                correct_answer: correctAnswer,
                is_correct: isCorrect,
                response_time_ms: responseTime,
                options: null,
                feedback: null,
            });
        }
    }

    const placeholder =
        session.answer_form === 'chinese'
            ? 'Type the Chinese…'
            : session.answer_form === 'pinyin'
              ? 'Type the pinyin…'
              : 'Type the English…';

    return (
        <div className="flex flex-col gap-6">
            <QuestionDisplay word={word} questionForm={session.question_form} />
            <div className="flex flex-col gap-3">
                <Input
                    ref={inputRef}
                    value={data.given_answer}
                    onChange={(e) => setData('given_answer', e.target.value)}
                    onKeyUp={(e) => e.key === 'Enter' && check()}
                    disabled={submitted}
                    placeholder={placeholder}
                    className="text-center text-lg"
                />
                <button
                    type="button"
                    onClick={check}
                    disabled={
                        submitted || checking || !data.given_answer.trim()
                    }
                    className="cursor-pointer rounded-lg bg-primary px-4 py-3 font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {checking ? 'Checking…' : 'Check'}
                </button>
            </div>
        </div>
    );
}
