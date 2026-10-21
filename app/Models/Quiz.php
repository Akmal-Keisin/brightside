<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['content_id', 'passing_score', 'max_attempts', 'time_limit_seconds', 'shuffle_questions', 'show_answers_after_submit'])]
class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'passing_score' => 'integer',
            'max_attempts' => 'integer',
            'time_limit_seconds' => 'integer',
            'shuffle_questions' => 'boolean',
            'show_answers_after_submit' => 'boolean',
        ];
    }

    /** @return BelongsTo<Content, $this> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /** @return HasMany<QuizQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<QuizAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
