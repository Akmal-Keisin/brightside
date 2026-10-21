<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MyCoursesController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function __invoke(Request $request): Response
    {
        $enrollments = $request->user()
            ->enrollments()
            ->active()
            ->with(['course.category'])
            ->latest('enrolled_at')
            ->paginate(9);

        return Inertia::render('learning/MyCourses', [
            'enrollments' => $enrollments->through(fn ($enrollment): array => [
                'id' => $enrollment->id,
                'progress_percent' => $enrollment->progress_percent,
                'completed_contents_count' => $enrollment->completed_contents_count,
                'total_contents_count' => $enrollment->total_contents_count,
                'enrolled_at' => $enrollment->enrolled_at?->format('M j, Y'),
                'course' => [
                    'title' => $enrollment->course->title,
                    'slug' => $enrollment->course->slug,
                    'subtitle' => $enrollment->course->subtitle,
                    'thumbnail_url' => $enrollment->course->thumbnail_path
                        ? Storage::disk('public')->url($enrollment->course->thumbnail_path)
                        : null,
                    'level' => $enrollment->course->level?->value,
                    'category' => $enrollment->course->category?->name,
                ],
            ]),
        ]);
    }
}
