<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentType;
use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Enums\VideoProvider;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Services\AuditLogger;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(CourseStatus::class)],
        ]);

        $courses = Course::query()
            ->with('category')
            ->withCount(['enrollments', 'chapters'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $search): Builder => $query
                ->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/courses/Index', [
            'courses' => $courses->through(fn (Course $course): array => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => $course->status?->value,
                'level' => $course->level?->value,
                'price' => $course->isFree() ? 'Free' : Money::format($course->price_minor, $course->currency),
                'category' => $course->category?->name,
                'enrollments_count' => $course->enrollments_count,
                'contents_count' => $course->contents_count,
                'chapters_count' => $course->chapters_count,
                'updated_at' => $course->updated_at?->format('M j, Y'),
            ]),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'statuses' => CourseStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/courses/Create', [
            'categories' => $this->categoryOptions(),
            'levels' => CourseLevel::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['status'] = CourseStatus::Draft;

        $course = DB::transaction(fn (): Course => Course::create($data));

        return to_route('admin.courses.edit', $course)
            ->with('success', 'Course created. Add chapters and lessons next.');
    }

    public function edit(Course $course): Response
    {
        $course->load(['chapters.contents.quiz.questions.options', 'category']);

        return Inertia::render('admin/courses/Edit', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'subtitle' => $course->subtitle,
                'description' => $course->description,
                'category_id' => $course->category_id,
                'price_minor' => $course->price_minor,
                'currency' => $course->currency,
                'level' => $course->level?->value,
                'status' => $course->status?->value,
                'trailer_url' => $course->trailer_url,
                'thumbnail_url' => $course->thumbnail_path
                    ? Storage::disk('public')->url($course->thumbnail_path)
                    : null,
                'thumbnail_alt' => $course->thumbnail_alt,
                'learning_outcomes' => implode("\n", $course->learning_outcomes ?? []),
                'requirements' => implode("\n", $course->requirements ?? []),
                'is_featured' => (bool) $course->is_featured,
                'contents_count' => $course->contents_count,
                'enrollments_count' => $course->enrollments_count,
                'chapters' => $course->chapters->map(fn ($chapter): array => [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'description' => $chapter->description,
                    'position' => $chapter->position,
                    'contents' => $chapter->contents->map(fn ($content): array => [
                        'id' => $content->id,
                        'title' => $content->title,
                        'type' => $content->type?->value,
                        'position' => $content->position,
                        'is_free_preview' => (bool) $content->is_free_preview,
                        'estimated_minutes' => (int) $content->estimated_minutes,
                        'body' => $content->body,
                        'video_provider' => $content->video_provider?->value,
                        'video_reference' => $content->video_reference,
                        'video_duration_seconds' => $content->video_duration_seconds,
                        'quiz' => $content->quiz ? [
                            'id' => $content->quiz->id,
                            'passing_score' => $content->quiz->passing_score,
                            'max_attempts' => $content->quiz->max_attempts,
                            'time_limit_seconds' => $content->quiz->time_limit_seconds,
                            'shuffle_questions' => (bool) $content->quiz->shuffle_questions,
                            'show_answers_after_submit' => (bool) $content->quiz->show_answers_after_submit,
                            'questions' => $content->quiz->questions->map(fn ($question): array => [
                                'id' => $question->id,
                                'question' => $question->question,
                                'explanation' => $question->explanation,
                                'points' => $question->points,
                                'options' => $question->options->map(fn ($option): array => [
                                    'id' => $option->id,
                                    'label' => $option->label,
                                    'is_correct' => (bool) $option->is_correct,
                                ])->all(),
                            ])->all(),
                        ] : null,
                    ])->all(),
                ])->all(),
            ],
            'categories' => $this->categoryOptions(),
            'levels' => CourseLevel::options(),
            'contentTypes' => ContentType::options(),
            'videoProviders' => VideoProvider::options(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $this->validated($request, $course);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }

            $data['thumbnail_path'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $old = $course->only(['title', 'price_minor', 'status', 'is_featured']);
        $course->update($data);

        $this->auditLogger->log(
            action: 'course.updated',
            subject: $course,
            oldValues: $old,
            newValues: $course->only(['title', 'price_minor', 'status', 'is_featured']),
        );

        return back()->with('success', 'Course details saved.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $hasPaidOrders = $course->orders()
            ->where('status', OrderStatus::Paid)
            ->exists();

        if ($course->enrollments()->exists() || $hasPaidOrders) {
            return back()->with(
                'error',
                'This course has learners or paid orders. Archive it instead so existing members keep access.',
            );
        }

        $this->auditLogger->log(
            action: 'course.deleted',
            subject: $course,
            oldValues: $course->only(['title', 'status']),
        );

        $course->delete();

        return to_route('admin.courses.index')
            ->with('success', 'Course moved to trash.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Course $course = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:240'],
            'description' => ['required', 'string', 'max:20000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price_minor' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'level' => ['required', Rule::enum(CourseLevel::class)],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'thumbnail_alt' => ['nullable', 'string', 'max:180'],
            'learning_outcomes' => ['nullable', 'string', 'max:5000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'is_featured' => ['boolean'],
        ]);

        $validated['learning_outcomes'] = $this->lines($validated['learning_outcomes'] ?? '');
        $validated['requirements'] = $this->lines($validated['requirements'] ?? '');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['currency'] = config('brightside.currency', 'IDR');
        unset($validated['thumbnail']);

        if (! $course) {
            $validated['slug'] = $this->uniqueSlug($validated['title']);
        }

        return $validated;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $suffix = 2;

        while (Course::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => ['id' => $category->id, 'name' => $category->name])
            ->all();
    }
}
