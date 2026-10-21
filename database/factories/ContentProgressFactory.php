<?php

namespace Database\Factories;

use App\Enums\ProgressStatus;
use App\Models\Content;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentProgress>
 */
class ContentProgressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'content_id' => Content::factory(),
            'status' => ProgressStatus::InProgress,
            'progress_seconds' => 0,
            'best_score' => null,
            'completed_at' => null,
            'last_viewed_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => ProgressStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
