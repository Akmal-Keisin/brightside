<?php

namespace App\Services;

use App\Enums\ProgressStatus;
use App\Models\Content;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function touch(Enrollment $enrollment, Content $content): ContentProgress
    {
        $progress = ContentProgress::firstOrNew([
            'enrollment_id' => $enrollment->id,
            'content_id' => $content->id,
        ]);

        $progress->fill([
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id,
            'last_viewed_at' => now(),
        ]);

        if (! $progress->exists) {
            $progress->status = ProgressStatus::InProgress;
        }

        $progress->save();

        $enrollment->forceFill(['last_accessed_at' => now()])->saveQuietly();

        return $progress;
    }

    public function complete(Enrollment $enrollment, Content $content, ?int $score = null): ContentProgress
    {
        return DB::transaction(function () use ($enrollment, $content, $score): ContentProgress {
            $progress = $this->touch($enrollment, $content);

            if ($score !== null) {
                $progress->best_score = max($progress->best_score ?? 0, $score);
            }

            if ($progress->status !== ProgressStatus::Completed) {
                $progress->status = ProgressStatus::Completed;
                $progress->completed_at = now();
            }

            $progress->save();

            $this->enrollments->recalculate($enrollment);

            return $progress->refresh();
        });
    }

    /**
     * Store video heartbeat progress and auto-complete at the configured ratio.
     */
    public function track(Enrollment $enrollment, Content $content, int $seconds): ContentProgress
    {
        $progress = $this->touch($enrollment, $content);

        $seconds = max(0, min($seconds, $content->video_duration_seconds ?: $seconds));

        $progress->progress_seconds = max($progress->progress_seconds, $seconds);
        $progress->save();

        $duration = $content->video_duration_seconds;

        if ($duration && $seconds >= (int) ceil($duration * config('brightside.video_completion_ratio', 0.9))) {
            return $this->complete($enrollment, $content);
        }

        return $progress->refresh();
    }
}
