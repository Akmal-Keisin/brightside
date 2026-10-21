<?php

namespace App\Models;

use Database\Factories\QuizOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_question_id', 'label', 'is_correct', 'position'])]
class QuizOption extends Model
{
    /** @use HasFactory<QuizOptionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    /** @return BelongsTo<QuizQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
