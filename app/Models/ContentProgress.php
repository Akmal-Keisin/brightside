<?php

namespace App\Models;

use App\Enums\ProgressStatus;
use Database\Factories\ContentProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $enrollment_id
 * @property int $user_id
 * @property int $course_id
 * @property int $content_id
 * @property ProgressStatus $status
 * @property int $progress_seconds
 * @property int|null $best_score
 * @property Carbon|null $completed_at
 */
#[Fillable([
    'enrollment_id', 'user_id', 'course_id', 'content_id', 'status',
    'progress_seconds', 'best_score', 'completed_at', 'last_viewed_at',
])]
class ContentProgress extends Model
{
    /** @use HasFactory<ContentProgressFactory> */
    use HasFactory;

    protected $table = 'content_progress';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ProgressStatus::class,
            'progress_seconds' => 'integer',
            'best_score' => 'integer',
            'completed_at' => 'datetime',
            'last_viewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<Content, $this> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
