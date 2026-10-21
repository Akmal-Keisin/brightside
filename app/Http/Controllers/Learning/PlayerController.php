<?php

namespace App\Http\Controllers\Learning;

use App\Enums\ProgressStatus;
use App\Enums\QuizAttemptStatus;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function resume(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        $enrollment = $this->enrollments->activeEnrollment($user, $course);

        if (! $enrollment) {
            if ($user->isAdmin()) {
                return to_route('admin.courses.preview', $course);
            }

            return to_route('courses.show', $course)
                ->with('error', 'Enroll in this course to open its lessons.');
        }

        $next = $this->enrollments->nextContent($enrollment)
            ?? $course->contents()->first();

        if (! $next) {
            return to_route('courses.show', $course)
                ->with('error', 'This course does not have any lessons yet.');
        }

        return to_route('learning.show', [$course, $next]);
    }

    public function show(Request $request, Course $course, Content $content): Response
    {
        $content->load(['chapter', 'quiz.questions.options']);

        if ($content->chapter->course_id !== $course->id) {
            abort(404);
        }

        $user = $request->user();
        $enrollment = $this->enrollments->activeEnrollment($user, $course);

        if (! $enrollment && ! $user->isAdmin()) {
            abort(403);
        }

        if ($enrollment) {
            app(ProgressService::class)->touch($enrollment, $content);
        }

        $chapters = $course->chapters()
            ->with(['contents' => fn ($query) => $query->with([
                'quiz' => fn ($query) => $query->select('id', 'content_id', 'passing_score', 'max_attempts', 'time_limit_seconds')->withCount('questions'),
            ])])
            ->get();

        $progress = $enrollment?->contentProgress()->get()->keyBy('content_id') ?? collect();

        $ordered = $chapters->flatMap->contents->values();
        $index = $ordered->search(fn (Content $item): bool => $item->id === $content->id);
        $previous = $index !== false && $index > 0 ? $ordered[$index - 1] : null;
        $next = $index !== false ? $ordered[$index + 1] ?? null : null;

        $contentPayload = $this->contentPayload($content);
        $contentPayload['is_completed'] = $progress->get($content->id)?->status === ProgressStatus::Completed;

        return Inertia::render('learning/Player', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'contents_count' => $course->contents_count,
            ],
            'chapters' => $chapters->map(fn (Chapter $chapter): array => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'contents' => $chapter->contents->map(fn (Content $item): array => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type?->value,
                    'estimated_minutes' => (int) $item->estimated_minutes,
                    'is_free_preview' => (bool) $item->is_free_preview,
                    'status' => $progress->get($item->id)?->status?->value,
                    'best_score' => $progress->get($item->id)?->best_score,
                    'quiz_questions_count' => (int) ($item->quiz?->questions_count ?? 0),
                ])->all(),
            ])->all(),
            'content' => $contentPayload,
            'navigation' => [
                'previous' => $previous ? ['id' => $previous->id, 'title' => $previous->title] : null,
                'next' => $next ? ['id' => $next->id, 'title' => $next->title] : null,
            ],
            'progress' => [
                'percent' => $enrollment?->progress_percent ?? 0,
                'completed' => $enrollment?->completed_contents_count ?? 0,
                'total' => $enrollment?->total_contents_count ?? $ordered->count(),
                'contents' => $progress->map(fn ($item): array => [
                    'content_id' => $item->content_id,
                    'status' => $item->status?->value,
                    'best_score' => $item->best_score,
                ])->values()->all(),
            ],
            'quiz' => $content->isQuiz() && $content->quiz
                ? $this->quizPayload($content, $enrollment)
                : null,
            'isPreview' => $enrollment === null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function contentPayload(Content $content): array
    {
        return [
            'id' => $content->id,
            'chapter_id' => $content->chapter_id,
            'chapter_title' => $content->chapter->title,
            'title' => $content->title,
            'type' => $content->type?->value,
            'estimated_minutes' => (int) $content->estimated_minutes,
            'body' => $content->isArticle() ? str((string) $content->body)->markdown() : null,
            'video_provider' => $content->video_provider?->value,
            'video_reference' => $content->video_reference,
            'video_duration_seconds' => $content->video_duration_seconds,
            'transcript' => $content->transcript,
            'is_completed' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quizPayload(Content $content, ?Enrollment $enrollment): array
    {
        $quiz = $content->quiz;

        $attempts = $enrollment
            ? $quiz->attempts()
                ->where('user_id', $enrollment->user_id)
                ->orderByDesc('attempt_number')
                ->get()
            : collect();

        $activeAttempt = $attempts->firstWhere('status', QuizAttemptStatus::InProgress);

        return [
            'id' => $quiz->id,
            'passing_score' => $quiz->passing_score,
            'max_attempts' => $quiz->max_attempts,
            'time_limit_seconds' => $quiz->time_limit_seconds,
            'show_answers_after_submit' => (bool) $quiz->show_answers_after_submit,
            'questions_count' => $quiz->questions->count(),
            'attempts_used' => $attempts->count(),
            'remaining_attempts' => $quiz->max_attempts === null ? null : max(0, $quiz->max_attempts - $attempts->count()),
            'best_score' => $attempts->max('score'),
            'passed' => $attempts->contains(fn ($attempt): bool => $attempt->status === QuizAttemptStatus::Passed),
            'active_attempt' => $activeAttempt ? ['id' => $activeAttempt->id] : null,
            'questions' => $quiz->questions->map(fn ($question): array => [
                'id' => $question->id,
                'question' => $question->question,
                'points' => (int) $question->points,
                'options' => $question->options->map(fn ($option): array => [
                    'id' => $option->id,
                    'label' => $option->label,
                ])->all(),
            ])->all(),
        ];
    }
}
