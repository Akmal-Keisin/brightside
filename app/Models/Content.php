<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Enums\VideoProvider;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $chapter_id
 * @property ContentType $type
 * @property string $title
 * @property int $position
 * @property bool $is_free_preview
 * @property int $estimated_minutes
 * @property string|null $body
 * @property VideoProvider|null $video_provider
 * @property string|null $video_reference
 */
#[Fillable([
    'chapter_id', 'type', 'title', 'position', 'is_free_preview', 'estimated_minutes',
    'body', 'video_provider', 'video_reference', 'video_duration_seconds',
    'captions_path', 'transcript',
])]
class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'is_free_preview' => 'boolean',
            'estimated_minutes' => 'integer',
            'video_provider' => VideoProvider::class,
            'video_duration_seconds' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function isArticle(): bool
    {
        return $this->type === ContentType::Article;
    }

    public function isVideo(): bool
    {
        return $this->type === ContentType::Video;
    }

    public function isQuiz(): bool
    {
        return $this->type === ContentType::Quiz;
    }

    /** @return BelongsTo<Chapter, $this> */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /** @return HasOne<Quiz, $this> */
    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function course(): Course
    {
        return $this->chapter->course;
    }
}
