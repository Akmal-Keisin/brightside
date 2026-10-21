<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\EnrollmentService;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly OrderService $orders,
    ) {}

    public function show(Request $request, Course $course): Response|RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        if ($this->enrollments->hasAccess($request->user(), $course)) {
            return to_route('learning.resume', $course);
        }

        if ($course->isFree()) {
            return to_route('courses.show', $course)
                ->with('info', 'This course is free — enroll from the course page.');
        }

        return Inertia::render('checkout/Show', [
            'course' => [
                'title' => $course->title,
                'slug' => $course->slug,
                'subtitle' => $course->subtitle,
                'price_minor' => $course->price_minor,
                'price' => Money::format($course->price_minor, $course->currency),
                'thumbnail_url' => $course->thumbnail_path
                    ? Storage::disk('public')->url($course->thumbnail_path)
                    : null,
                'contents_count' => $course->contents_count,
                'duration_minutes' => (int) round($course->duration_seconds / 60),
                'level' => $course->level?->value,
            ],
            'customer' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
            'gatewayConfigured' => filled(config('brightside.midtrans.server_key')) || app()->environment('local', 'testing'),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $order = $this->orders->create($request->user(), $course);

        if (! $order->payment_url) {
            return to_route('checkout.status', $order)
                ->with('error', 'Payment could not be started. Please try again.');
        }

        return redirect()->away($order->payment_url);
    }
}
