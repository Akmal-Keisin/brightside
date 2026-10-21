<?php

use App\Enums\ProgressStatus;
use App\Enums\QuizAttemptStatus;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;

function quizFixture(int $maxAttempts = 3): array
{
    $course = Course::factory()->free()->create();
    $chapter = Chapter::factory()->create(['course_id' => $course->id]);
    $content = Content::factory()->quiz()->create(['chapter_id' => $chapter->id]);
    $quiz = $content->quiz;

    $question = $quiz->questions()->create([
        'question' => 'Which token name is most useful?',
        'position' => 1,
        'points' => 1,
    ]);
    $correct = $question->options()->create(['label' => 'color-action', 'is_correct' => true, 'position' => 1]);
    $question->options()->create(['label' => 'blue-600', 'is_correct' => false, 'position' => 2]);

    $user = User::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'total_contents_count' => 1,
    ]);

    if ($maxAttempts !== 3) {
        $quiz->update(['max_attempts' => $maxAttempts]);
    }

    return [$user, $course, $content, $quiz, $question, $correct, $enrollment];
}

test('a member can start and pass a quiz', function () {
    [$user, $course, $content, , $question, $correct, $enrollment] = quizFixture();

    $this->actingAs($user)
        ->post(route('learning.quiz.start', [$course, $content]))
        ->assertRedirect();

    $attempt = QuizAttempt::query()->firstOrFail();

    $this->actingAs($user)
        ->post(route('learning.quiz.submit', $attempt), [
            'answers' => [$question->id => $correct->id],
        ])
        ->assertRedirect(route('learning.quiz.show', $attempt));

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Passed)
        ->and($attempt->fresh()->score)->toBe(100)
        ->and($attempt->fresh()->passed)->toBeTrue();

    $this->assertDatabaseHas('content_progress', [
        'enrollment_id' => $enrollment->id,
        'content_id' => $content->id,
        'status' => ProgressStatus::Completed->value,
    ]);
});

test('a failed quiz does not complete the lesson', function () {
    [$user, $course, $content, , $question, , $enrollment] = quizFixture();

    $this->actingAs($user)->post(route('learning.quiz.start', [$course, $content]));

    $attempt = QuizAttempt::query()->firstOrFail();
    $wrong = $question->options()->where('is_correct', false)->firstOrFail();

    $this->actingAs($user)->post(route('learning.quiz.submit', $attempt), [
        'answers' => [$question->id => $wrong->id],
    ]);

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Failed)
        ->and($attempt->fresh()->score)->toBe(0);

    $this->assertDatabaseMissing('content_progress', [
        'enrollment_id' => $enrollment->id,
        'content_id' => $content->id,
        'status' => ProgressStatus::Completed->value,
    ]);
});

test('a quiz respects its attempt limit', function () {
    [$user, $course, $content] = quizFixture(maxAttempts: 1);

    $this->actingAs($user)->post(route('learning.quiz.start', [$course, $content]));
    $attempt = QuizAttempt::query()->firstOrFail();
    $attempt->update(['status' => QuizAttemptStatus::Failed]);

    $this->actingAs($user)
        ->from(route('learning.show', [$course, $content]))
        ->post(route('learning.quiz.start', [$course, $content]))
        ->assertRedirect(route('learning.show', [$course, $content]))
        ->assertSessionHasErrors('quiz');
});

test('members cannot submit another member attempt', function () {
    [$user, $course, $content] = quizFixture();

    $this->actingAs($user)->post(route('learning.quiz.start', [$course, $content]));
    $attempt = QuizAttempt::query()->firstOrFail();

    $this->actingAs(User::factory()->create())
        ->post(route('learning.quiz.submit', $attempt), ['answers' => []])
        ->assertNotFound();
});

test('a quiz submission requires an answer for every question', function () {
    [$user, $course, $content] = quizFixture();

    $this->actingAs($user)->post(route('learning.quiz.start', [$course, $content]));
    $attempt = QuizAttempt::query()->firstOrFail();

    $this->actingAs($user)
        ->from(route('learning.show', [$course, $content]))
        ->post(route('learning.quiz.submit', $attempt), ['answers' => []])
        ->assertRedirect(route('learning.show', [$course, $content]))
        ->assertSessionHasErrors('answers');

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::InProgress);
});

test('a quiz submission rejects options from another question', function () {
    [$user, $course, $content, , $question] = quizFixture();

    $this->actingAs($user)->post(route('learning.quiz.start', [$course, $content]));
    $attempt = QuizAttempt::query()->firstOrFail();

    $this->actingAs($user)
        ->from(route('learning.show', [$course, $content]))
        ->post(route('learning.quiz.submit', $attempt), [
            'answers' => [$question->id => 999999],
        ])
        ->assertSessionHasErrors('answers');

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::InProgress);
});
