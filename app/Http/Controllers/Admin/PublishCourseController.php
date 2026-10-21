<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentType;
use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PublishCourseController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function store(Course $course): RedirectResponse
    {
        $course->load([
            'category',
            'chapters.contents.quiz.questions.options',
        ]);

        $errors = [];

        if (blank($course->title)) {
            $errors['title'] = 'Add a course title before publishing.';
        }

        if (blank($course->slug)) {
            $errors['slug'] = 'The course needs a valid URL slug.';
        }

        if (blank($course->description)) {
            $errors['description'] = 'Add a course description before publishing.';
        }

        if (blank($course->thumbnail_path)) {
            $errors['thumbnail'] = 'Upload a cover image before publishing.';
        }

        if (! $course->category_id || ! $course->category) {
            $errors['category'] = 'Choose a category before publishing.';
        }

        if ($course->price_minor < 0) {
            $errors['price'] = 'Set a valid price of zero or more.';
        }

        $contents = $course->chapters->flatMap->contents;

        if ($course->chapters->isEmpty()) {
            $errors['curriculum'] = 'Add at least one chapter.';
        }

        if ($contents->isEmpty()) {
            $errors['curriculum'] = 'Add at least one lesson.';
        }

        foreach ($contents as $content) {
            if ($content->type === ContentType::Video && (blank($content->video_provider) || blank($content->video_reference))) {
                $errors['curriculum'] = 'Every video lesson needs a provider and reference.';
            }

            if ($content->type !== ContentType::Quiz) {
                continue;
            }

            $quiz = $content->quiz;

            if (! $quiz || $quiz->questions->isEmpty()) {
                $errors['curriculum'] = 'Every quiz lesson needs at least one question.';

                continue;
            }

            foreach ($quiz->questions as $question) {
                $optionCount = $question->options->count();
                $correctCount = $question->options->where('is_correct', true)->count();

                if ($optionCount < 2 || $optionCount > 6) {
                    $errors['curriculum'] = 'Each quiz question needs between two and six options.';
                }

                if ($correctCount !== 1) {
                    $errors['curriculum'] = 'Each quiz question needs exactly one correct answer.';
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $old = $course->status;

        $course->forceFill([
            'status' => CourseStatus::Published,
            'published_at' => $course->published_at ?? now(),
        ])->save();

        $this->auditLogger->log(
            action: 'course.published',
            subject: $course,
            oldValues: ['status' => $old?->value],
            newValues: ['status' => CourseStatus::Published->value],
        );

        return back()->with('success', 'Course is live.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $old = $course->status;

        $course->forceFill(['status' => CourseStatus::Draft])->save();

        $this->auditLogger->log(
            action: 'course.unpublished',
            subject: $course,
            oldValues: ['status' => $old?->value],
            newValues: ['status' => CourseStatus::Draft->value],
        );

        return back()->with('success', 'Course moved back to draft.');
    }
}
