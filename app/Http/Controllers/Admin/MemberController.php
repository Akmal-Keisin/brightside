<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $members = User::query()
            ->where('role', 'Member')
            ->when($filters['q'] ?? null, fn (Builder $query, string $search): Builder => $query
                ->where(fn (Builder $query): Builder => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->withCount(['enrollments' => fn (Builder $query) => $query->active()])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/members/Index', [
            'members' => $members->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'headline' => $user->headline,
                'enrollments_count' => $user->enrollments_count,
                'joined_at' => $user->created_at?->format('M j, Y'),
                'last_login_at' => $user->last_login_at?->diffForHumans(),
            ]),
            'filters' => ['q' => $filters['q'] ?? ''],
        ]);
    }

    public function show(User $user): Response
    {
        abort_unless($user->isMember(), 404);

        $user->load(['enrollments.course']);

        return Inertia::render('admin/members/Show', [
            'member' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'headline' => $user->headline,
                'bio' => $user->bio,
                'joined_at' => $user->created_at?->format('M j, Y'),
                'last_login_at' => $user->last_login_at?->format('M j, Y · H:i'),
                'orders_count' => $user->orders()->count(),
                'enrollments' => $user->enrollments->map(fn (Enrollment $enrollment): array => [
                    'id' => $enrollment->id,
                    'course_title' => $enrollment->course->title,
                    'course_slug' => $enrollment->course->slug,
                    'source' => $enrollment->source?->value,
                    'progress_percent' => $enrollment->progress_percent,
                    'is_active' => $enrollment->isActive(),
                    'enrolled_at' => $enrollment->enrolled_at?->format('M j, Y'),
                ])->all(),
            ],
            'courses' => Course::query()
                ->where('status', 'Published')
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (Course $course): array => ['id' => $course->id, 'title' => $course->title]),
        ]);
    }
}
