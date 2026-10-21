<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;

class CoursePreviewController extends Controller
{
    public function __invoke(Course $course): RedirectResponse
    {
        $content = $course->contents()->first();

        if (! $content) {
            return to_route('admin.courses.edit', $course)
                ->with('error', 'Add a lesson before previewing this course.');
        }

        return to_route('learning.show', [$course, $content])
            ->with('info', 'Admin preview — progress is not recorded.');
    }
}
