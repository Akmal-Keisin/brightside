<?php

namespace App\Services;

use App\Enums\EnrollmentSource;
use App\Enums\ProgressStatus;
use App\Models\Content;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\EnrollmentConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * Enroll a member in a course, reactivating a revoked enrollment if needed.
     */
    public function enroll(
        User $user,
        Course $course,
        EnrollmentSource $source,
        ?Order $order = null,
        ?string $reason = null,
    ): Enrollment {
        return DB::transaction(function () use ($user, $course, $source, $order, $reason): Enrollment {
            $enrollment = Enrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            $wasActive = $enrollment?->isActive() ?? false;

            if ($enrollment && $wasActive) {
                return $enrollment;
            }

            $attributes = [
                'order_id' => $order?->id,
                'source' => $source,
                'enrolled_at' => now(),
                'expires_at' => null,
                'revoked_at' => null,
                'total_contents_count' => $course->contents()->count(),
            ];

            if ($enrollment) {
                $enrollment->update($attributes);
            } else {
                $enrollment = Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    ...$attributes,
                ]);
            }

            if (! $wasActive) {
                $course->increment('enrollments_count');
            }

            $this->auditLogger->log(
                action: $source === EnrollmentSource::Purchase ? 'enrollment.purchased' : 'enrollment.granted',
                subject: $enrollment,
                newValues: [
                    'course_id' => $course->id,
                    'source' => $source->value,
                    'order_id' => $order?->id,
                ],
                reason: $reason,
                actor: $user,
            );

            $enrollment->refresh();

            if (! $wasActive) {
                EnrollmentConfirmedNotification::sendFor($user, $course);
            }

            return $enrollment;
        });
    }

    /**
     * Free enrollment for a published, zero-priced course.
     */
    public function enrollForFree(User $user, Course $course): Enrollment
    {
        if (! $course->isPublished() || ! $course->isFree()) {
            throw ValidationException::withMessages([
                'course' => 'This course is not available for free enrollment.',
            ]);
        }

        return $this->enroll($user, $course, EnrollmentSource::Free);
    }

    public function hasAccess(User $user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->enrollments()
            ->active()
            ->where('course_id', $course->id)
            ->exists();
    }

    public function activeEnrollment(User $user, Course $course): ?Enrollment
    {
        return $user->enrollments()
            ->active()
            ->where('course_id', $course->id)
            ->first();
    }

    /**
     * Recalculate totals from completed content rows.
     */
    public function recalculate(Enrollment $enrollment): Enrollment
    {
        $total = $enrollment->course->contents()->count();

        $completed = $enrollment->contentProgress()
            ->where('status', ProgressStatus::Completed)
            ->count();

        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $enrollment->forceFill([
            'total_contents_count' => $total,
            'completed_contents_count' => $completed,
            'progress_percent' => $percent,
            'completed_at' => $total > 0 && $completed >= $total
                ? ($enrollment->completed_at ?? now())
                : null,
            'last_accessed_at' => now(),
        ])->save();

        return $enrollment->refresh();
    }

    /**
     * Recalculate course-level counters from published curriculum.
     */
    public function refreshCourseCounters(Course $course): void
    {
        $contents = $course->contents();

        $course->forceFill([
            'contents_count' => $contents->count(),
            'duration_seconds' => (int) $contents->sum('estimated_minutes') * 60,
        ])->save();
    }

    public function revoke(Enrollment $enrollment, ?string $reason = null): void
    {
        $enrollment->forceFill(['revoked_at' => now()])->save();

        $this->auditLogger->log(
            action: 'enrollment.revoked',
            subject: $enrollment,
            oldValues: ['revoked_at' => null],
            newValues: ['revoked_at' => $enrollment->revoked_at?->toIso8601String()],
            reason: $reason,
        );
    }

    /**
     * Determine the next incomplete content, falling back to the first lesson.
     */
    public function nextContent(Enrollment $enrollment): ?Content
    {
        $completedIds = $enrollment->contentProgress()
            ->where('status', ProgressStatus::Completed)
            ->pluck('content_id')
            ->all();

        return $enrollment->course->contents()
            ->orderBy('chapters.position')
            ->orderBy('contents.position')
            ->get()
            ->first(fn (Content $content): bool => ! in_array($content->id, $completedIds, true));
    }

    /**
     * Progress payload for a course curriculum.
     *
     * @return array<int, ContentProgress>
     */
    public function progressMap(Enrollment $enrollment): array
    {
        return $enrollment->contentProgress()
            ->get()
            ->keyBy('content_id')
            ->all();
    }
}
