<?php

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use Database\Factories\QuizAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quiz_id
 * @property int $user_id
 * @property int $enrollment_id
 * @property int $attempt_number
 * @property QuizAttemptStatus $status
 * @property int|null $score
 * @property bool|null $passed
 * @property array<int, int>|null $question_order
 * @property Carbon $started_at
 * @property Carbon|null $deadline_at
 * @property Carbon|null $completed_at
 */
#[Fillable([
    'quiz_id', 'user_id', 'enrollment_id', 'attempt_number', 'status', 'score',
    'passed', 'question_order', 'started_at', 'deadline_at', 'completed_at',
])]
class QuizAttempt extends Model
{
    /** @use HasFactory<QuizAttemptFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => QuizAttemptStatus::class,
            'score' => 'integer',
            'passed' => 'boolean',
            'question_order' => 'array',
            'started_at' => 'datetime',
            'deadline_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [QuizAttemptStatus::Submitted, QuizAttemptStatus::Passed, QuizAttemptStatus::Failed], true);
    }

    /** @return BelongsTo<Quiz, $this> */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return HasMany<QuizAttemptAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
