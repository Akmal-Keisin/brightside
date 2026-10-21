<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseCardResource;
use App\Http\Resources\CourseDetailResource;
use App\Models\Category;
use App\Models\Content;
use App\Models\Course;
use App\Services\EnrollmentService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:90'],
            'level' => ['nullable', 'string', 'in:'.implode(',', array_column(CourseLevel::cases(), 'value'))],
            'price' => ['nullable', 'string', 'in:free,paid'],
            'sort' => ['nullable', 'string', 'in:newest,popular,price_low,price_high'],
        ]);

        $courses = Course::query()
            ->published()
            ->with(['category', 'author'])
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(fn (Builder $query): Builder => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug): Builder => $query
                ->whereHas('category', fn (Builder $query): Builder => $query->where('slug', $slug)))
            ->when($filters['level'] ?? null, fn (Builder $query, string $level): Builder => $query->where('level', $level))
            ->when(($filters['price'] ?? null) === 'free', fn (Builder $query): Builder => $query->where('price_minor', 0))
            ->when(($filters['price'] ?? null) === 'paid', fn (Builder $query): Builder => $query->where('price_minor', '>', 0))
            ->when(($filters['sort'] ?? 'newest') === 'popular', fn (Builder $query): Builder => $query->orderByDesc('enrollments_count'))
            ->when(($filters['sort'] ?? 'newest') === 'price_low', fn (Builder $query): Builder => $query->orderBy('price_minor'))
            ->when(($filters['sort'] ?? 'newest') === 'price_high', fn (Builder $query): Builder => $query->orderByDesc('price_minor'))
            ->when(($filters['sort'] ?? 'newest') === 'newest', fn (Builder $query): Builder => $query->orderByDesc('published_at'))
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('catalog/Index', [
            'courses' => CourseCardResource::collection($courses),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'level' => $filters['level'] ?? '',
                'price' => $filters['price'] ?? '',
                'sort' => $filters['sort'] ?? 'newest',
            ],
            'categories' => Category::query()
                ->ordered()
                ->withCount(['courses' => fn ($query) => $query->where('status', CourseStatus::Published)])
                ->get()
                ->map(fn (Category $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'color' => $category->color,
                    'courses_count' => $category->courses_count,
                ]),
            'levels' => CourseLevel::options(),
        ]);
    }

    public function show(Request $request, Course $course): Response
    {
        if (! $course->isPublished() && ! $request->user()?->isAdmin()) {
            abort(404);
        }

        $course->load([
            'category',
            'author',
            'chapters.contents.quiz.questions',
        ]);

        $enrollment = $request->user()
            ? $this->enrollments->activeEnrollment($request->user(), $course)
            : null;

        $related = Course::query()
            ->published()
            ->whereKeyNot($course->id)
            ->when($course->category_id, fn (Builder $query): Builder => $query->where('category_id', $course->category_id))
            ->with(['category', 'author'])
            ->limit(3)
            ->get();

        $description = Str::limit(strip_tags((string) ($course->subtitle ?: $course->description)), 155);
        $price = Money::isFree($course->price_minor)
            ? 0
            : ($course->currency === 'IDR' ? $course->price_minor : $course->price_minor / 100);

        return Inertia::render('catalog/Show', [
            'course' => (new CourseDetailResource($course))->resolve($request),
            'seo' => [
                'title' => $course->title,
                'description' => $description,
                'canonical' => route('courses.show', $course),
                'image' => $course->thumbnail_path
                    ? Storage::disk('public')->url($course->thumbnail_path)
                    : null,
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Course',
                    'name' => $course->title,
                    'description' => $description,
                    'url' => route('courses.show', $course),
                    'image' => $course->thumbnail_path
                        ? Storage::disk('public')->url($course->thumbnail_path)
                        : null,
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => 'Brightside',
                        'url' => route('home'),
                    ],
                    'inLanguage' => $course->language ?: 'en',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $price,
                        'priceCurrency' => $course->currency,
                        'availability' => 'https://schema.org/InStock',
                        'url' => route('courses.show', $course),
                    ],
                ],
            ],
            'enrollment' => $enrollment ? [
                'id' => $enrollment->id,
                'progress_percent' => $enrollment->progress_percent,
            ] : null,
            'isEnrolled' => $enrollment !== null || (bool) $request->user()?->isAdmin(),
            'canCheckout' => $request->user() !== null
                && $enrollment === null
                && ! $request->user()->isAdmin()
                && $course->isPublished()
                && ! $course->isFree(),
            'related' => CourseCardResource::collection($related)->resolve(),
        ]);
    }

    public function preview(Request $request, Course $course, Content $content): Response|RedirectResponse
    {
        abort_unless($content->chapter()->where('course_id', $course->id)->exists(), 404);
        abort_unless($course->isPublished(), 404);

        $isAdmin = (bool) $request->user()?->isAdmin();
        $canPreview = $isAdmin || ($content->is_free_preview && ! $content->isQuiz());

        if (! $canPreview) {
            return to_route('courses.show', $course)
                ->with('error', 'This lesson is not a free preview. Enrol to open the full course.');
        }

        $content->load(['chapter', 'quiz']);

        return Inertia::render('catalog/Preview', [
            'course' => [
                'title' => $course->title,
                'slug' => $course->slug,
            ],
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $content->type?->value,
                'body' => $content->isArticle() ? str((string) $content->body)->markdown() : null,
                'video_provider' => $content->video_provider?->value,
                'video_reference' => $content->video_reference,
                'estimated_minutes' => $content->estimated_minutes,
            ],
        ]);
    }
}
