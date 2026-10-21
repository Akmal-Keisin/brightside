<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Services\AuditLogger;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $chapter = $course->chapters()->create([
            ...$validated,
            'position' => (int) $course->chapters()->max('position') + 1,
        ]);

        $this->enrollments->refreshCourseCounters($course);

        $this->auditLogger->log(
            action: 'chapter.created',
            subject: $chapter,
            newValues: $chapter->only(['title', 'position']),
        );

        return back()->with('success', 'Chapter added.');
    }

    public function update(Request $request, Chapter $chapter): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $chapter->update($validated);

        return back()->with('success', 'Chapter updated.');
    }

    public function destroy(Chapter $chapter): RedirectResponse
    {
        $course = $chapter->course;

        $chapter->delete();

        $this->enrollments->refreshCourseCounters($course);

        $this->auditLogger->log(
            action: 'chapter.deleted',
            subject: $chapter,
            oldValues: $chapter->only(['title', 'position']),
        );

        return back()->with('success', 'Chapter deleted.');
    }
}
