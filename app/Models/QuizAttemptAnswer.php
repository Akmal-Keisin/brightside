<?php

namespace App\Models;

use Database\Factories\QuizAttemptAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quiz_attempt_id', 'quiz_question_id', 'quiz_option_id', 'question_text',
    'selected_option_text', 'correct_option_text', 'is_correct',
])]
class QuizAttemptAnswer extends Model
{
    /** @use HasFactory<QuizAttemptAnswerFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    /** @return BelongsTo<QuizAttempt, $this> */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }
}
