<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Enums\VideoProvider;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'type' => ContentType::Article,
            'title' => fake()->sentence(5),
            'position' => fake()->numberBetween(1, 10),
            'is_free_preview' => false,
            'estimated_minutes' => fake()->numberBetween(3, 20),
            'body' => fake()->paragraphs(3, true),
            'video_provider' => null,
            'video_reference' => null,
            'video_duration_seconds' => null,
            'captions_path' => null,
            'transcript' => null,
        ];
    }

    public function video(): static
    {
        return $this->state(fn (): array => [
            'type' => ContentType::Video,
            'body' => null,
            'video_provider' => VideoProvider::YouTube,
            'video_reference' => 'dQw4w9WgXcQ',
            'video_duration_seconds' => 600,
        ]);
    }

    public function freePreview(): static
    {
        return $this->state(fn (): array => [
            'is_free_preview' => true,
        ]);
    }

    public function quiz(): static
    {
        return $this->state(fn (): array => [
            'type' => ContentType::Quiz,
            'body' => null,
            'is_free_preview' => false,
        ])->afterCreating(function (Content $content): void {
            Quiz::factory()->create(['content_id' => $content->id]);
        });
    }
}
