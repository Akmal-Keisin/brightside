<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseCardResource;
use App\Models\Course;
use App\Services\EnrollmentService;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function __invoke(): Response
    {
        $user = request()->user();

        $active = $user->enrollments()
            ->active()
            ->with(['course.category', 'course.author'])
            ->latest('last_accessed_at')
            ->get();

        $continue = $active->first();

        $next = $continue ? $this->enrollments->nextContent($continue) : null;

        $recommended = Course::query()
            ->published()
            ->whereNotIn('id', $active->pluck('course_id'))
            ->with(['category', 'author'])
            ->orderByDesc('is_featured')
            ->orderByDesc('enrollments_count')
            ->limit(3)
            ->get();

        return Inertia::render('learning/Dashboard', [
            'stats' => [
                'active_courses' => $active->count(),
                'completed_courses' => $user->enrollments()->whereNotNull('completed_at')->count(),
                'completed_lessons' => $user->contentProgress()->where('status', 'Completed')->count(),
            ],
            'continueLearning' => $continue ? [
                'enrollment_id' => $continue->id,
                'course' => [
                    'title' => $continue->course->title,
                    'slug' => $continue->course->slug,
                    'category' => $continue->course->category?->name,
                    'thumbnail_url' => $continue->course->thumbnail_path
                        ? Storage::disk('public')->url($continue->course->thumbnail_path)
                        : null,
                ],
                'progress_percent' => $continue->progress_percent,
                'next_content' => $next ? ['id' => $next->id, 'title' => $next->title] : null,
            ] : null,
            'enrollments' => $active->map(fn ($enrollment): array => [
                'id' => $enrollment->id,
                'progress_percent' => $enrollment->progress_percent,
                'completed_contents_count' => $enrollment->completed_contents_count,
                'total_contents_count' => $enrollment->total_contents_count,
                'last_accessed_at' => $enrollment->last_accessed_at?->diffForHumans(),
                'course' => [
                    'title' => $enrollment->course->title,
                    'slug' => $enrollment->course->slug,
                    'thumbnail_url' => $enrollment->course->thumbnail_path
                        ? Storage::disk('public')->url($enrollment->course->thumbnail_path)
                        : null,
                    'level' => $enrollment->course->level?->value,
                    'category' => $enrollment->course->category?->name,
                ],
            ])->values()->all(),
            'recommended' => CourseCardResource::collection($recommended)->resolve(),
        ]);
    }
}
