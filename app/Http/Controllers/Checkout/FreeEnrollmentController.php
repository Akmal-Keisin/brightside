<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreeEnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->enrollments->enrollForFree($request->user(), $course);

        return to_route('learning.resume', $course)
            ->with('success', 'You are enrolled. Enjoy the course!');
    }
}
