<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly ProgressService $progress,
    ) {}

    public function complete(Request $request, Course $course, Content $content): RedirectResponse
    {
        $enrollment = $this->enrollmentOrFail($request, $course);
        $this->ensureContentBelongs($course, $content);

        if ($content->isQuiz()) {
            abort(422, 'Pass the quiz to complete this lesson.');
        }

        $wasComplete = $enrollment->completed_at !== null;

        $this->progress->complete($enrollment, $content);
        $enrollment->refresh();

        $message = ! $wasComplete && $enrollment->completed_at !== null
            ? 'Course complete. That is a big one — nice work.'
            : 'Lesson complete. Nice work.';

        return back()->with('success', $message);
    }

    public function track(Request $request, Course $course, Content $content): JsonResponse
    {
        $validated = $request->validate([
            'seconds' => ['required', 'integer', 'min:0', 'max:86400'],
        ]);

        $enrollment = $this->enrollmentOrFail($request, $course);
        $this->ensureContentBelongs($course, $content);

        $progress = $this->progress->track($enrollment, $content, $validated['seconds']);

        return response()->json([
            'status' => $progress->status?->value,
            'progress_seconds' => $progress->progress_seconds,
            'course_progress' => $enrollment->refresh()->progress_percent,
        ]);
    }

    private function enrollmentOrFail(Request $request, Course $course): Enrollment
    {
        $enrollment = $this->enrollments->activeEnrollment($request->user(), $course);

        abort_if($enrollment === null, 403, 'Enroll in this course to track progress.');

        return $enrollment;
    }

    private function ensureContentBelongs(Course $course, Content $content): void
    {
        abort_unless($content->chapter->course_id === $course->id, 404);
    }
}
