<?php

namespace App\Http\Resources;

use App\Models\Course;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Course */
class CourseCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'thumbnail_url' => $this->thumbnail_path
                ? Storage::disk('public')->url($this->thumbnail_path)
                : null,
            'price_minor' => $this->price_minor,
            'price' => Money::isFree($this->price_minor) ? 'Free' : Money::format($this->price_minor, $this->currency),
            'currency' => $this->currency,
            'level' => $this->level?->value,
            'is_free' => $this->isFree(),
            'is_new' => $this->isNew(),
            'is_featured' => (bool) $this->is_featured,
            'rating_avg' => (float) $this->rating_avg,
            'rating_count' => (int) $this->rating_count,
            'enrollments_count' => (int) $this->enrollments_count,
            'contents_count' => (int) $this->contents_count,
            'duration_minutes' => (int) round(((int) $this->duration_seconds) / 60),
            'category' => $this->whenLoaded('category', fn (): ?array => $this->category ? [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'color' => $this->category->color,
            ] : null),
            'author' => $this->whenLoaded('author', fn (): ?array => $this->author ? [
                'name' => $this->author->name,
            ] : null),
        ];
    }
}
