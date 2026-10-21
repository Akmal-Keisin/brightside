<?php

namespace App\Models;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $category_id
 * @property int|null $created_by
 * @property string $title
 * @property string $slug
 * @property string|null $subtitle
 * @property string $description
 * @property string|null $thumbnail_path
 * @property int $price_minor
 * @property CourseLevel $level
 * @property CourseStatus $status
 * @property Carbon|null $published_at
 * @property int $contents_count
 * @property int $duration_seconds
 * @property bool $is_featured
 */
#[Fillable([
    'category_id', 'created_by', 'title', 'slug', 'subtitle', 'description',
    'thumbnail_path', 'thumbnail_alt', 'trailer_url', 'price_minor', 'currency',
    'level', 'language', 'learning_outcomes', 'requirements', 'status',
    'published_at', 'duration_seconds', 'contents_count', 'enrollments_count',
    'is_featured',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'learning_outcomes' => 'array',
            'requirements' => 'array',
            'level' => CourseLevel::class,
            'status' => CourseStatus::class,
            'published_at' => 'datetime',
            'duration_seconds' => 'integer',
            'contents_count' => 'integer',
            'enrollments_count' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
            if (blank($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }

    public function isArchived(): bool
    {
        return $this->status === CourseStatus::Archived;
    }

    public function isFree(): bool
    {
        return $this->price_minor === 0;
    }

    public function isPurchasable(): bool
    {
        return $this->isPublished() && ! $this->isFree();
    }

    public function isNew(): bool
    {
        return $this->published_at?->gt(now()->subDays(14)) ?? false;
    }

    /** @param Builder<Course> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::Published)->whereNotNull('published_at');
    }

    /** @param Builder<Course> $query */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<Chapter, $this> */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasManyThrough<Content, Chapter, $this> */
    public function contents(): HasManyThrough
    {
        return $this->hasManyThrough(Content::class, Chapter::class, 'course_id', 'chapter_id')
            ->orderBy('chapters.position')
            ->orderBy('contents.position');
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
