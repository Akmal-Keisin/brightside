<?php

namespace App\Http\Resources;

use App\Models\Course;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** @mixin Course */
class CourseDetailResource extends CourseCardResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->description,
            'trailer_url' => $this->trailer_url,
            'thumbnail_alt' => $this->thumbnail_alt,
            'language' => $this->language,
            'learning_outcomes' => $this->learning_outcomes ?? [],
            'requirements' => $this->requirements ?? [],
            'updated_at' => $this->updated_at?->toIso8601String(),
            'thumbnail' => $this->thumbnail_path
                ? Storage::disk('public')->url($this->thumbnail_path)
                : null,
            'chapters' => $this->whenLoaded('chapters', fn (): array => $this->chapters->map(fn ($chapter): array => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'description' => $chapter->description,
                'contents' => $chapter->contents->map(fn ($content): array => [
                    'id' => $content->id,
                    'title' => $content->title,
                    'type' => $content->type?->value,
                    'estimated_minutes' => (int) $content->estimated_minutes,
                    'is_free_preview' => (bool) $content->is_free_preview,
                    'quiz_questions' => $content->relationLoaded('quiz') ? $content->quiz?->questions->count() : null,
                ])->all(),
            ])->all()),
            'total_lessons' => $this->whenLoaded('contents', fn (): int => $this->contents->count()),
            'price_display' => Money::isFree($this->price_minor) ? 'Free' : Money::format($this->price_minor, $this->currency),
        ];
    }
}
