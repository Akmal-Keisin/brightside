<?php

namespace Database\Factories;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'category_id' => Category::factory(),
            'created_by' => User::factory()->admin(),
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'subtitle' => fake()->sentence(8),
            'description' => fake()->paragraphs(4, true),
            'thumbnail_path' => null,
            'thumbnail_alt' => null,
            'trailer_url' => null,
            'price_minor' => 149000,
            'currency' => 'IDR',
            'level' => CourseLevel::Beginner,
            'language' => 'en',
            'learning_outcomes' => [
                fake()->sentence(8),
                fake()->sentence(8),
                fake()->sentence(8),
            ],
            'requirements' => [
                fake()->sentence(6),
            ],
            'status' => CourseStatus::Published,
            'published_at' => now(),
            'duration_seconds' => 0,
            'contents_count' => 0,
            'enrollments_count' => 0,
            'is_featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => CourseStatus::Archived,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (): array => [
            'price_minor' => 0,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'is_featured' => true,
        ]);
    }
}
