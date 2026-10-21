<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentSource;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isMember(), 404);

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $this->enrollments->enroll(
            $user,
            $course,
            EnrollmentSource::Grant,
            reason: $validated['reason'],
        );

        return back()->with('success', 'Enrollment granted.');
    }

    public function destroy(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->enrollments->revoke($enrollment, $validated['reason']);

        return back()->with('success', 'Enrollment revoked.');
    }
}
