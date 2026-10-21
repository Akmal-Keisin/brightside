<?php

use App\Enums\CourseStatus;
use App\Enums\EnrollmentSource;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\EnrollmentConfirmedNotification;
use Illuminate\Support\Facades\Notification;

test('members cannot enter the admin studio', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admins can open the studio', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('a course cannot be published without a curriculum', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::factory()->draft()->create(['description' => 'A useful course.']);

    $this->actingAs($admin)
        ->from(route('admin.courses.edit', $course))
        ->post(route('admin.courses.publish', $course))
        ->assertRedirect(route('admin.courses.edit', $course))
        ->assertSessionHasErrors('curriculum');

    expect($course->fresh()->status)->toBe(CourseStatus::Draft);
});

test('a category with courses cannot be deleted', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    Course::factory()->create(['category_id' => $category->id]);

    $this->actingAs($admin)
        ->from(route('admin.categories.index'))
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a course with learners cannot be deleted', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::factory()->create();
    Enrollment::factory()->create([
        'user_id' => User::factory()->create()->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.courses.edit', $course))
        ->delete(route('admin.courses.destroy', $course))
        ->assertRedirect(route('admin.courses.edit', $course))
        ->assertSessionHas('error');

    expect($course->fresh())->not->toBeNull();
});

test('a complete course can be published and unpublished', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::factory()->draft()->create([
        'description' => 'A useful course.',
        'thumbnail_path' => 'courses/test-cover.jpg',
        'thumbnail_alt' => 'Test cover',
    ]);
    $chapter = Chapter::factory()->create(['course_id' => $course->id]);
    Content::factory()->create(['chapter_id' => $chapter->id]);

    $this->actingAs($admin)
        ->post(route('admin.courses.publish', $course))
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Published)
        ->and($course->fresh()->published_at)->not->toBeNull();

    $this->actingAs($admin)
        ->post(route('admin.courses.unpublish', $course))
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Draft);
});

test('an admin grant requires a reason and notifies the member', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.members.show', $member))
        ->post(route('admin.enrollments.store', $member), [
            'course_id' => $course->id,
        ])
        ->assertRedirect(route('admin.members.show', $member))
        ->assertSessionHasErrors('reason');

    expect(Enrollment::query()->where('user_id', $member->id)->exists())->toBeFalse();

    $this->actingAs($admin)
        ->post(route('admin.enrollments.store', $member), [
            'course_id' => $course->id,
            'reason' => 'Community scholarship',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $member->id,
        'course_id' => $course->id,
        'source' => EnrollmentSource::Grant->value,
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'enrollment.granted',
        'reason' => 'Community scholarship',
    ]);

    Notification::assertSentOnDemandTimes(EnrollmentConfirmedNotification::class, 1);
});

test('revoking an enrollment requires a reason', function () {
    $admin = User::factory()->admin()->create();
    $enrollment = Enrollment::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.members.show', $enrollment->user_id))
        ->delete(route('admin.enrollments.destroy', $enrollment))
        ->assertSessionHasErrors('reason');

    expect($enrollment->fresh()->revoked_at)->toBeNull();

    $this->actingAs($admin)
        ->delete(route('admin.enrollments.destroy', $enrollment), [
            'reason' => 'Duplicate purchase',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($enrollment->fresh()->revoked_at)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'enrollment.revoked',
        'reason' => 'Duplicate purchase',
    ]);
});

test('a lesson type cannot be changed after creation', function () {
    $admin = User::factory()->admin()->create();
    $course = Course::factory()->create();
    $chapter = Chapter::factory()->create(['course_id' => $course->id]);
    $content = Content::factory()->create(['chapter_id' => $chapter->id]);

    $this->actingAs($admin)
        ->from(route('admin.courses.edit', $course))
        ->put(route('admin.contents.update', $content), [
            'title' => $content->title,
            'type' => 'Video',
            'estimated_minutes' => 5,
        ])
        ->assertSessionHasErrors('type');

    expect($content->fresh()->type->value)->toBe('Article');
});
