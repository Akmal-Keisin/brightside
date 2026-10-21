<?php

namespace Database\Factories;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttemptAnswer>
 */
class QuizAttemptAnswerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_attempt_id' => QuizAttempt::factory(),
            'quiz_question_id' => QuizQuestion::factory(),
            'quiz_option_id' => null,
            'question_text' => fake()->sentence(10).'?',
            'selected_option_text' => fake()->sentence(5),
            'correct_option_text' => fake()->sentence(5),
            'is_correct' => false,
        ];
    }
}
