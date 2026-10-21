<?php

use App\Enums\CourseStatus;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the home page shows only published courses', function () {
    $published = Course::factory()->featured()->create();
    Course::factory()->draft()->featured()->create();
    Course::factory()->archived()->featured()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('catalog/Home')
            ->has('featured', 1)
            ->where('featured.0.title', $published->title)
            ->has('popular', 1)
        );
});

test('the catalog lists published courses and hides drafts', function () {
    $published = Course::factory()->create();
    Course::factory()->draft()->create();

    $this->get(route('courses.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('catalog/Index')
            ->has('courses.data', 1)
            ->where('courses.data.0.title', $published->title)
        );
});

test('the catalog can be searched by title', function () {
    Course::factory()->create(['title' => 'Design Systems That Ship']);
    Course::factory()->create(['title' => 'Laravel for Product Teams']);

    $this->get(route('courses.index', ['q' => 'design']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Design Systems That Ship')
        );
});

test('the catalog can be filtered by price', function () {
    $free = Course::factory()->free()->create(['title' => 'Free Course']);
    Course::factory()->create(['title' => 'Paid Course']);

    $this->get(route('courses.index', ['price' => 'free']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', $free->title)
        );

    $this->get(route('courses.index', ['price' => 'paid']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Paid Course')
        );
});

test('a draft course is hidden from guests but visible to an admin', function () {
    $draft = Course::factory()->draft()->create();

    $this->get(route('courses.show', $draft))->assertNotFound();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('courses.show', $draft))
        ->assertOk();
});

test('only free preview lessons are public', function () {
    $course = Course::factory()->draft()->create();
    $free = Content::factory()->freePreview()->create([
        'chapter_id' => Chapter::factory()->create(['course_id' => $course->id])->id,
    ]);
    $locked = Content::factory()->create([
        'chapter_id' => $free->chapter_id,
    ]);

    $course->update(['status' => CourseStatus::Published, 'published_at' => now()]);

    $this->get(route('courses.preview', [$course, $free]))->assertOk();
    $this->get(route('courses.preview', [$course, $locked]))
        ->assertRedirect(route('courses.show', $course))
        ->assertSessionHas('error');
});

test('the sitemap lists published course urls and excludes drafts', function () {
    $published = Course::factory()->create(['title' => 'Published Sitemap Course']);
    $draft = Course::factory()->draft()->create(['title' => 'Draft Sitemap Course']);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('courses.show', $published), false)
        ->assertDontSee(route('courses.show', $draft), false);
});
