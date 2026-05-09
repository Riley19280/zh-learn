import { UnsavedPracticeAttempt } from '@/types';
import { useEffect, useRef } from 'react';
import { AlertCircle, CheckCircle2, ChevronRight, XCircle } from 'lucide-react';

export function FeedbackBar({
    attempt,
    ttsUrl,
    onContinue,
}: {
    attempt: UnsavedPracticeAttempt;
    ttsUrl?: string | null;
    onContinue: () => void;
}) {
    const continueRef = useRef<HTMLButtonElement>(null);
    const isPartial = attempt.is_correct && !!attempt.feedback;

    useEffect(() => {
        continueRef.current?.focus();
    }, []);

    const colors = attempt.is_correct
        ? isPartial
            ? { bg: 'bg-yellow-50 dark:bg-yellow-950/50', text: 'text-yellow-700 dark:text-yellow-300', btn: 'bg-yellow-500 hover:bg-yellow-600' }
            : { bg: 'bg-green-50 dark:bg-green-950/50', text: 'text-green-700 dark:text-green-300', btn: 'bg-green-600 hover:bg-green-700' }
        : { bg: 'bg-red-50 dark:bg-red-950/50', text: 'text-red-600 dark:text-red-300', btn: 'bg-red-500 hover:bg-red-600' };

    return (
        <div className={`mt-4 flex items-center justify-between rounded-lg px-5 py-4 ${colors.bg}`}>
            <div className="flex items-center gap-2">
                {attempt.is_correct ? (
                    isPartial
                        ? <AlertCircle className="size-5 text-yellow-500 dark:text-yellow-400" />
                        : <CheckCircle2 className="size-5 text-green-600 dark:text-green-400" />
                ) : (
                    <XCircle className="size-5 text-red-500 dark:text-red-400" />
                )}
                <div className={`font-medium ${colors.text}`}>
                    {attempt.is_correct ? (
                        <>
                            Correct!
                            {attempt.feedback && (
                                <p className="mt-1 text-sm font-normal">{attempt.feedback}</p>
                            )}
                        </>
                    ) : (
                        <>
                            <span>
                                Answer:{' '}
                                <span
                                    className={ttsUrl ? 'cursor-pointer underline-offset-2 hover:underline' : ''}
                                    onClick={() => ttsUrl && new Audio(ttsUrl).play()}
                                >
                                    {attempt.correct_answer}
                                </span>
                            </span>
                            {attempt.feedback && (
                                <p className="mt-1 text-sm font-normal">{attempt.feedback}</p>
                            )}
                        </>
                    )}
                </div>
            </div>
            <button
                ref={continueRef}
                type="button"
                onClick={onContinue}
                className={`flex cursor-pointer items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors ${colors.btn}`}
            >
                Continue <ChevronRight className="size-4" />
            </button>
        </div>
    );
}
