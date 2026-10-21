<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class ArchiveCourseController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function store(Course $course): RedirectResponse
    {
        $old = $course->status;

        $course->forceFill(['status' => CourseStatus::Archived])->save();

        $this->auditLogger->log(
            action: 'course.archived',
            subject: $course,
            oldValues: ['status' => $old?->value],
            newValues: ['status' => CourseStatus::Archived->value],
        );

        return back()->with('success', 'Course archived. Existing learners keep access.');
    }
}
