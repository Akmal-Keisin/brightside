<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuizAttemptStatus;
use App\Http\Controllers\Controller;
use App\Models\Content;
use Inertia\Inertia;
use Inertia\Response;

class QuizReportController extends Controller
{
    public function __invoke(Content $content): Response
    {
        if (! $content->isQuiz()) {
            abort(404);
        }

        $content->loadMissing(['quiz', 'chapter.course']);

        if (! $content->quiz) {
            abort(404);
        }

        $quiz = $content->quiz;
        $attempts = $quiz->attempts()
            ->with('user')
            ->latest()
            ->get();

        $attemptCount = $attempts->count();
        $completedCount = $attempts
            ->filter(fn ($attempt): bool => $attempt->status !== QuizAttemptStatus::InProgress)
            ->count();
        $passedCount = $attempts->where('passed', true)->count();

        return Inertia::render('admin/quizzes/Report', [
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
            ],
            'course' => [
                'title' => $content->chapter->course->title,
                'slug' => $content->chapter->course->slug,
            ],
            'quiz' => [
                'passing_score' => $quiz->passing_score,
                'max_attempts' => $quiz->max_attempts,
            ],
            'stats' => [
                'attempts_count' => $attemptCount,
                'completed_count' => $completedCount,
                'passed_count' => $passedCount,
                'pass_rate' => $attemptCount > 0 ? (int) round(($passedCount / $attemptCount) * 100) : 0,
                'average_score' => $attemptCount > 0 ? (int) round((float) $attempts->avg('score')) : 0,
            ],
            'attempts' => $attempts->map(fn ($attempt): array => [
                'id' => $attempt->id,
                'member' => $attempt->user?->name ?? 'Deleted member',
                'email' => $attempt->user?->email,
                'attempt_number' => $attempt->attempt_number,
                'score' => $attempt->score,
                'status' => $attempt->status?->value,
                'status_label' => $attempt->status?->label(),
                'passed' => $attempt->passed,
                'completed_at' => $attempt->completed_at?->format('M j, Y · H:i'),
                'started_at' => $attempt->started_at?->format('M j, Y · H:i'),
            ])->all(),
        ]);
    }
}
