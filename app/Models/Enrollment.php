<?php

namespace App\Models;

use App\Enums\EnrollmentSource;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $course_id
 * @property int|null $order_id
 * @property EnrollmentSource $source
 * @property Carbon $enrolled_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $revoked_at
 * @property Carbon|null $last_accessed_at
 * @property Carbon|null $completed_at
 * @property int $progress_percent
 * @property int $completed_contents_count
 * @property int $total_contents_count
 */
#[Fillable([
    'user_id', 'course_id', 'order_id', 'source', 'enrolled_at', 'expires_at',
    'revoked_at', 'last_accessed_at', 'completed_at', 'progress_percent',
    'completed_contents_count', 'total_contents_count',
])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'source' => EnrollmentSource::class,
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_percent' => 'integer',
            'completed_contents_count' => 'integer',
            'total_contents_count' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** @param Builder<Enrollment> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(fn (Builder $query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return HasMany<ContentProgress, $this> */
    public function contentProgress(): HasMany
    {
        return $this->hasMany(ContentProgress::class);
    }

    /** @return HasMany<QuizAttempt, $this> */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
