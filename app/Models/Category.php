<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'color', 'position'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /** @return HasMany<Course, $this> */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /** @param Builder<Category> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
