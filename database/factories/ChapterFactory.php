<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'position' => fake()->numberBetween(1, 10),
        ];
    }
}
