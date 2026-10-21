<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_id' => Content::factory()->state([
                'type' => ContentType::Quiz,
                'body' => null,
            ]),
            'passing_score' => 70,
            'max_attempts' => 3,
            'time_limit_seconds' => null,
            'shuffle_questions' => false,
            'show_answers_after_submit' => true,
        ];
    }
}
