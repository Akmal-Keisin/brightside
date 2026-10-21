<?php

namespace App\Models;

use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'title', 'description', 'position'])]
class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return HasMany<Content, $this> */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class)->orderBy('position')->orderBy('id');
    }
}
