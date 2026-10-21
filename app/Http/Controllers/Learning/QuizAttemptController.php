<?php

namespace App\Http\Controllers\Learning;

use App\Enums\QuizAttemptStatus;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Services\EnrollmentService;
use App\Services\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class QuizAttemptController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly QuizService $quizzes,
    ) {}

    public function store(Request $request, Course $course, Content $content): RedirectResponse
    {
        $enrollment = $this->enrollments->activeEnrollment($request->user(), $course);

        if (! $enrollment) {
            abort(403);
        }

        abort_unless($content->chapter->course_id === $course->id, 404);

        $quiz = $content->quiz;

        if (! $quiz) {
            throw ValidationException::withMessages(['quiz' => 'This lesson is not a quiz.']);
        }

        $attempt = $this->quizzes->start($enrollment, $content, $quiz);

        return to_route('learning.quiz.show', $attempt);
    }

    public function submit(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $user = $request->user();

        abort_unless($attempt->user_id === $user->id, 404);

        $attempt->load(['quiz.content.chapter.course', 'enrollment']);

        $validated = $request->validate([
            'answers' => ['array'],
            'answers.*' => ['nullable', 'integer'],
            'auto_submitted' => ['boolean'],
        ]);

        $this->quizzes->submit(
            $attempt->enrollment,
            $attempt,
            $validated['answers'] ?? [],
        );

        return to_route('learning.quiz.show', $attempt)
            ->with('success', 'Quiz submitted.');
    }

    public function show(Request $request, QuizAttempt $attempt): Response
    {
        abort_unless($attempt->user_id === $request->user()->id, 404);

        $attempt->load([
            'quiz.content.chapter.course',
            'quiz.questions.options',
            'answers',
        ]);

        $quiz = $attempt->quiz;
        $content = $quiz->content;
        $course = $content->chapter->course;
        $inProgress = $attempt->status === QuizAttemptStatus::InProgress;
        $orderedIds = $attempt->question_order ?? [];
        $orderedQuestions = collect($orderedIds)
            ->map(fn (int $id) => $quiz->questions->firstWhere('id', $id))
            ->filter()
            ->values();
        $questions = $inProgress
            ? ($orderedQuestions->isNotEmpty() ? $orderedQuestions : $quiz->questions->shuffle())
            : collect();

        $review = $attempt->answers->map(fn ($answer): array => [
            'question' => $answer->question_text,
            'selected' => $answer->selected_option_text,
            'correct' => $attempt->passed || $quiz->show_answers_after_submit ? $answer->correct_option_text : null,
            'is_correct' => $answer->is_correct,
        ])->all();

        $allowance = $this->quizzes->attemptAllowance($quiz, $attempt->enrollment);

        return Inertia::render('learning/QuizResult', [
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status?->value,
                'score' => $attempt->score,
                'passed' => $attempt->passed,
                'attempt_number' => $attempt->attempt_number,
                'completed_at' => $attempt->completed_at?->toIso8601String(),
                'deadline_at' => $attempt->deadline_at?->toIso8601String(),
            ],
            'quiz' => [
                'passing_score' => $quiz->passing_score,
                'show_answers_after_submit' => (bool) $quiz->show_answers_after_submit,
                'remaining_attempts' => $allowance['remaining'],
                'max_attempts' => $quiz->max_attempts,
            ],
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
            ],
            'course' => [
                'title' => $course->title,
                'slug' => $course->slug,
            ],
            'review' => $review,
            'inProgress' => $inProgress,
            'questions' => $questions->map(fn ($question): array => [
                'id' => $question->id,
                'question' => $question->question,
                'points' => (int) $question->points,
                'options' => $question->options->map(fn ($option): array => [
                    'id' => $option->id,
                    'label' => $option->label,
                ])->all(),
            ])->all(),
            'answers' => $attempt->answers->pluck('quiz_option_id', 'quiz_question_id'),
        ]);
    }
}
