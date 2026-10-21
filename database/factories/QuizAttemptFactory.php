<?php

namespace Database\Factories;

use App\Enums\QuizAttemptStatus;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'enrollment_id' => Enrollment::factory(),
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::InProgress,
            'score' => null,
            'passed' => null,
            'question_order' => null,
            'started_at' => now(),
            'deadline_at' => null,
            'completed_at' => null,
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (): array => [
            'status' => QuizAttemptStatus::Passed,
            'score' => 90,
            'passed' => true,
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => QuizAttemptStatus::Failed,
            'score' => 40,
            'passed' => false,
            'completed_at' => now(),
        ]);
    }
}
