<?php

namespace App\Models;

use Database\Factories\QuizQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['quiz_id', 'question', 'explanation', 'position', 'points'])]
class QuizQuestion extends Model
{
    /** @use HasFactory<QuizQuestionFactory> */
    use HasFactory;

    /** @return BelongsTo<Quiz, $this> */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /** @return HasMany<QuizOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('position')->orderBy('id');
    }
}
