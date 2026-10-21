<?php

namespace Database\Factories;

use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'order_id' => null,
            'source' => EnrollmentSource::Free,
            'enrolled_at' => now(),
            'expires_at' => null,
            'revoked_at' => null,
            'last_accessed_at' => null,
            'completed_at' => null,
            'progress_percent' => 0,
            'completed_contents_count' => 0,
            'total_contents_count' => 0,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'revoked_at' => now(),
        ]);
    }
}
