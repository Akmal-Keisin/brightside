<?php

use App\Enums\ProgressStatus;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

function enrolledCourse(): array
{
    $course = Course::factory()->free()->create();
    $chapter = Chapter::factory()->create(['course_id' => $course->id]);
    $article = Content::factory()->create(['chapter_id' => $chapter->id]);
    $enrollment = Enrollment::factory()->create([
        'user_id' => User::factory()->create()->id,
        'course_id' => $course->id,
        'total_contents_count' => 1,
    ]);

    return [$enrollment->user, $course, $chapter, $article, $enrollment];
}

test('a member can complete an article lesson', function () {
    [$user, $course, , $article, $enrollment] = enrolledCourse();

    $this->actingAs($user)
        ->post(route('learning.complete', [$course, $article]))
        ->assertRedirect()
        ->assertSessionHas('success', 'Course complete. That is a big one — nice work.');

    $progress = ContentProgress::query()
        ->where('enrollment_id', $enrollment->id)
        ->where('content_id', $article->id)
        ->firstOrFail();

    expect($progress->status)->toBe(ProgressStatus::Completed)
        ->and($enrollment->fresh()->progress_percent)->toBe(100)
        ->and($enrollment->fresh()->completed_at)->not->toBeNull();
});

test('a member must be enrolled to complete a lesson', function () {
    $course = Course::factory()->create();
    $article = Content::factory()->create([
        'chapter_id' => Chapter::factory()->create(['course_id' => $course->id])->id,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('learning.complete', [$course, $article]))
        ->assertForbidden();
});

test('video progress auto-completes at the configured threshold', function () {
    [$user, $course, , , $enrollment] = enrolledCourse();
    $video = Content::factory()->video()->create([
        'chapter_id' => $course->chapters()->first()->id,
        'video_duration_seconds' => 100,
    ]);
    $enrollment->forceFill(['total_contents_count' => 2])->save();

    $this->actingAs($user)
        ->postJson(route('learning.progress', [$course, $video]), ['seconds' => 95])
        ->assertOk()
        ->assertJsonPath('status', 'Completed');

    expect($enrollment->fresh()->progress_percent)->toBe(50);
});

test('a quiz lesson cannot be completed by clicking the article button', function () {
    [$user, $course, $chapter] = enrolledCourse();
    $quizContent = Content::factory()->quiz()->create(['chapter_id' => $chapter->id]);

    $this->actingAs($user)
        ->post(route('learning.complete', [$course, $quizContent]))
        ->assertStatus(422);
});
