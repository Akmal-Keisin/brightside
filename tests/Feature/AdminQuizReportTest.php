<?php

use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;

test('an admin can view a quiz attempt report', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['name' => 'Rani Putri', 'email' => 'rani@example.com']);
    $course = Course::factory()->create();
    $chapter = Chapter::factory()->create(['course_id' => $course->id]);
    $content = Content::factory()->quiz()->create(['chapter_id' => $chapter->id]);

    QuizAttempt::factory()->passed()->create([
        'quiz_id' => $content->quiz->id,
        'user_id' => $member->id,
        'score' => 90,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.quizzes.report', $content))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/quizzes/Report')
            ->where('content.title', $content->title)
            ->where('stats.attempts_count', 1)
            ->where('stats.pass_rate', 100)
            ->where('stats.average_score', 90)
            ->where('attempts.0.member', 'Rani Putri')
            ->where('attempts.0.score', 90)
        );
});

test('members cannot view a quiz attempt report', function () {
    $course = Course::factory()->create();
    $chapter = Chapter::factory()->create(['course_id' => $course->id]);
    $content = Content::factory()->quiz()->create(['chapter_id' => $chapter->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.quizzes.report', $content))
        ->assertForbidden();
});

test('a report cannot be opened for a non-quiz lesson', function () {
    $admin = User::factory()->admin()->create();
    $content = Content::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.quizzes.report', $content))
        ->assertNotFound();
});
