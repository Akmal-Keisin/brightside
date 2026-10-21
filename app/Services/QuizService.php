<?php

namespace App\Services;

use App\Enums\ProgressStatus;
use App\Enums\QuizAttemptStatus;
use App\Models\Content;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function __construct(
        private readonly ProgressService $progress,
    ) {}

    public function start(Enrollment $enrollment, Content $content, Quiz $quiz): QuizAttempt
    {
        $inProgress = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $enrollment->user_id)
            ->where('status', QuizAttemptStatus::InProgress)
            ->latest('id')
            ->first();

        if ($inProgress) {
            return $inProgress;
        }

        $attemptCount = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $enrollment->user_id)
            ->count();

        if ($quiz->max_attempts !== null && $attemptCount >= $quiz->max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => 'You have used all attempts for this quiz.',
            ]);
        }

        $questionIds = $quiz->questions()->pluck('id')->all();

        if ($questionIds === []) {
            throw ValidationException::withMessages([
                'quiz' => 'This quiz does not have any questions yet.',
            ]);
        }

        if ($quiz->shuffle_questions) {
            shuffle($questionIds);
        }

        return QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'attempt_number' => $attemptCount + 1,
            'status' => QuizAttemptStatus::InProgress,
            'question_order' => $questionIds,
            'started_at' => now(),
            'deadline_at' => $quiz->time_limit_seconds
                ? now()->addSeconds($quiz->time_limit_seconds)
                : null,
        ]);
    }

    /**
     * Grade and submit a quiz attempt.
     *
     * @param  array<int|string, int|string|null>  $answers  Question ID => option ID.
     */
    public function submit(Enrollment $enrollment, QuizAttempt $attempt, array $answers): QuizAttempt
    {
        if ($attempt->status !== QuizAttemptStatus::InProgress) {
            throw ValidationException::withMessages([
                'quiz' => 'This attempt has already been submitted.',
            ]);
        }

        if ($attempt->deadline_at?->addSeconds(config('brightside.quiz_grace_seconds', 30))->isPast()) {
            $attempt->forceFill([
                'status' => QuizAttemptStatus::Expired,
                'completed_at' => now(),
            ])->save();

            throw ValidationException::withMessages([
                'quiz' => 'The time limit for this attempt has passed.',
            ]);
        }

        return DB::transaction(function () use ($enrollment, $attempt, $answers): QuizAttempt {
            $quiz = $attempt->quiz()->with('questions.options')->firstOrFail();

            $questions = collect($quiz->question_order ?: $quiz->questions->pluck('id')->all())
                ->map(fn (int $id): ?QuizQuestion => $quiz->questions->firstWhere('id', $id))
                ->filter();

            $expectedKeys = $questions->pluck('id')->map(fn (int $id): int => $id)->sort()->values()->all();
            $answerKeys = collect(array_keys($answers))->map(fn ($key): int => (int) $key)->sort()->values()->all();

            if ($expectedKeys !== $answerKeys) {
                throw ValidationException::withMessages([
                    'answers' => 'Answer every question with one option from this quiz.',
                ]);
            }

            foreach ($questions as $question) {
                $optionId = $answers[$question->id] ?? null;
                $selected = $optionId ? $question->options->firstWhere('id', (int) $optionId) : null;

                if (! $selected) {
                    throw ValidationException::withMessages([
                        'answers' => 'Answer every question with one option from this quiz.',
                    ]);
                }
            }

            $earned = 0;
            $total = 0;

            foreach ($questions as $question) {
                $total += $question->points;

                $selectedId = $answers[$question->id] ?? null;
                $selected = $selectedId ? $question->options->firstWhere('id', (int) $selectedId) : null;
                $correct = $question->options->firstWhere('is_correct', true);

                if ($selected?->is_correct) {
                    $earned += $question->points;
                }

                QuizAttemptAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt->id,
                        'quiz_question_id' => $question->id,
                    ],
                    [
                        'quiz_option_id' => $selected?->id,
                        'question_text' => $question->question,
                        'selected_option_text' => $selected?->label,
                        'correct_option_text' => $correct?->label,
                        'is_correct' => (bool) $selected?->is_correct,
                    ],
                );
            }

            $score = $total > 0 ? (int) round(($earned / $total) * 100) : 0;
            $passed = $score >= $quiz->passing_score;

            $attempt->forceFill([
                'status' => $passed ? QuizAttemptStatus::Passed : QuizAttemptStatus::Failed,
                'score' => $score,
                'passed' => $passed,
                'completed_at' => now(),
            ])->save();

            if ($passed) {
                $this->progress->complete($enrollment, $quiz->content, $score);
            } else {
                $progress = $this->progress->touch($enrollment, $quiz->content);
                $progress->best_score = max($progress->best_score ?? 0, $score);
                $progress->save();
            }

            return $attempt->refresh()->load('answers');
        });
    }

    /**
     * Number of attempts used against the configured limit.
     *
     * @return array{used: int, remaining: int|null}
     */
    public function attemptAllowance(Quiz $quiz, Enrollment $enrollment): array
    {
        $used = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $enrollment->user_id)
            ->count();

        return [
            'used' => $used,
            'remaining' => $quiz->max_attempts === null ? null : max(0, $quiz->max_attempts - $used),
        ];
    }

    public function bestScore(Quiz $quiz, Enrollment $enrollment): ?int
    {
        return QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $enrollment->user_id)
            ->whereNotNull('score')
            ->max('score');
    }

    public function isPassed(Quiz $quiz, Enrollment $enrollment): bool
    {
        return QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $enrollment->user_id)
            ->where('status', QuizAttemptStatus::Passed)
            ->whereHas('answers')
            ->exists();
    }

    public function markCompleted(Enrollment $enrollment, Content $content): void
    {
        $this->progress->complete($enrollment, $content);
    }

    /**
     * Whether every question has been completed for progress display.
     */
    public function contentIsComplete(Enrollment $enrollment, Content $content): bool
    {
        return $enrollment->contentProgress()
            ->where('content_id', $content->id)
            ->where('status', ProgressStatus::Completed)
            ->exists();
    }
}
