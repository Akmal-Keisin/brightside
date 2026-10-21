<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = filled($validated['from'] ?? null)
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = filled($validated['to'] ?? null)
            ? Carbon::parse($validated['to'])->endOfDay()
            : now()->endOfDay();

        $paidOrders = Order::query()
            ->where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$from, $to]);

        $ordersInRange = Order::query()->whereBetween('created_at', [$from, $to]);

        $enrollmentsInRange = Enrollment::query()->whereBetween('enrolled_at', [$from, $to]);
        $enrollmentCount = (clone $enrollmentsInRange)->count();
        $completedCount = (clone $enrollmentsInRange)->whereNotNull('completed_at')->count();
        $completionRate = $enrollmentCount > 0
            ? round(($completedCount / $enrollmentCount) * 100, 1)
            : 0;

        return Inertia::render('admin/Dashboard', [
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'stats' => [
                'revenue' => Money::format((int) (clone $paidOrders)->sum('total_minor')),
                'paid_orders' => (clone $paidOrders)->count(),
                'orders' => (clone $ordersInRange)->count(),
                'pending_orders' => (clone $ordersInRange)->where('status', OrderStatus::Pending)->count(),
                'members' => User::query()->where('role', 'Member')->count(),
                'enrollments' => (clone $enrollmentsInRange)->active()->count(),
                'completions' => $completedCount,
                'completion_rate' => $completionRate,
                'published_courses' => Course::query()->where('status', CourseStatus::Published)->count(),
                'draft_courses' => Course::query()->where('status', CourseStatus::Draft)->count(),
            ],
            'recent_orders' => Order::query()
                ->with('user')
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (Order $order): array => [
                    'order_number' => $order->order_number,
                    'customer' => $order->customer_name,
                    'course' => $order->course_title,
                    'amount' => Money::format($order->total_minor, $order->currency),
                    'status' => $order->status?->value,
                    'status_label' => $order->status?->label(),
                ]),
            'top_courses' => Course::query()
                ->with('category')
                ->orderByDesc('enrollments_count')
                ->limit(5)
                ->get()
                ->map(fn (Course $course): array => [
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'status' => $course->status?->value,
                    'enrollments_count' => $course->enrollments_count,
                    'category' => $course->category?->name,
                ]),
            'enrollments_by_day' => Enrollment::query()
                ->selectRaw('DATE(enrolled_at) as day, count(*) as total')
                ->whereBetween('enrolled_at', [$from, $to])
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->map(fn ($row): array => ['day' => $row->day, 'total' => (int) $row->total]),
        ]);
    }
}
