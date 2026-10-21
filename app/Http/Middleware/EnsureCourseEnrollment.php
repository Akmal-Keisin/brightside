<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Services\EnrollmentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourseEnrollment
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $course = $request->route('course');

        if (! $course instanceof Course) {
            abort(404);
        }

        $user = $request->user();

        if (! $user || ! $this->enrollments->hasAccess($user, $course)) {
            return to_route('courses.show', $course)
                ->with('error', 'Enroll in this course to open its lessons.');
        }

        $request->attributes->set(
            'enrollment',
            $user->isAdmin() ? null : $this->enrollments->activeEnrollment($user, $course),
        );

        return $next($request);
    }
}
