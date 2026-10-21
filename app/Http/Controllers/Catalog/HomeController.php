<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseCardResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featured = Course::query()
            ->published()
            ->featured()
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $popular = Course::query()
            ->published()
            ->with(['category', 'author'])
            ->orderByDesc('enrollments_count')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $categories = Category::query()
            ->ordered()
            ->withCount(['courses' => fn ($query) => $query->where('status', CourseStatus::Published)])
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'color' => $category->color,
                'description' => $category->description,
                'courses_count' => $category->courses_count,
            ]);

        return Inertia::render('catalog/Home', [
            'featured' => CourseCardResource::collection($featured)->resolve(),
            'popular' => CourseCardResource::collection($popular)->resolve(),
            'categories' => $categories,
            'stats' => [
                'courses' => Course::query()->published()->count(),
                'learners' => User::query()->where('role', 'Member')->count(),
                'lessons' => (int) Course::query()->published()->sum('contents_count'),
            ],
            'seo' => [
                'title' => 'Brightside — Learn something good',
                'description' => 'Friendly, practical courses for curious people. Learn at your own pace and keep every lesson for life.',
            ],
        ]);
    }
}
