<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentType;
use App\Enums\VideoProvider;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Content;
use App\Services\AuditLogger;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function store(Request $request, Chapter $chapter): RedirectResponse
    {
        $validated = $this->validated($request);

        $content = $chapter->contents()->create([
            ...$validated,
            'position' => (int) $chapter->contents()->max('position') + 1,
        ]);

        if ($content->isQuiz()) {
            $content->quiz()->create([
                'passing_score' => 70,
                'show_answers_after_submit' => true,
            ]);
        }

        $this->enrollments->refreshCourseCounters($chapter->course);

        $this->auditLogger->log(
            action: 'content.created',
            subject: $content,
            newValues: $content->only(['title', 'type', 'position']),
        );

        return back()->with('success', 'Lesson added.');
    }

    public function update(Request $request, Content $content): RedirectResponse
    {
        if ($request->filled('type') && $request->input('type') !== $content->type->value) {
            throw ValidationException::withMessages([
                'type' => 'A lesson type cannot change after creation. Delete and recreate the lesson instead.',
            ]);
        }

        $validated = $this->validated($request, $content);
        $validated['type'] = $content->type->value;

        $content->update($validated);

        if ($content->isQuiz() && ! $content->quiz) {
            $content->quiz()->create(['passing_score' => 70, 'show_answers_after_submit' => true]);
        }

        $this->enrollments->refreshCourseCounters($content->chapter->course);

        $this->auditLogger->log(
            action: 'content.updated',
            subject: $content,
            newValues: $content->only(['title', 'type', 'is_free_preview']),
        );

        return back()->with('success', 'Lesson saved.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        $course = $content->chapter->course;

        $content->delete();

        $this->enrollments->refreshCourseCounters($course);

        $this->auditLogger->log(
            action: 'content.deleted',
            subject: $content,
            oldValues: $content->only(['title', 'type', 'position']),
        );

        return back()->with('success', 'Lesson deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Content $content = null): array
    {
        $type = $request->input('type', $content?->type?->value);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::enum(ContentType::class)],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'is_free_preview' => ['boolean'],
            'body' => ['nullable', 'string', 'max:50000'],
            'video_provider' => ['nullable', Rule::enum(VideoProvider::class)],
            'video_reference' => ['nullable', 'string', 'max:255'],
            'video_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'transcript' => ['nullable', 'string', 'max:50000'],
        ]);

        $validated['is_free_preview'] = $request->boolean('is_free_preview');
        $validated['estimated_minutes'] = $validated['estimated_minutes'] ?? 5;

        if ($type === ContentType::Article->value && blank($validated['body'] ?? null)) {
            throw ValidationException::withMessages(['body' => 'Article lessons need a body.']);
        }

        if ($type === ContentType::Video->value) {
            if (blank($validated['video_provider'] ?? null) || blank($validated['video_reference'] ?? null)) {
                throw ValidationException::withMessages([
                    'video_reference' => 'Video lessons need a provider and reference.',
                ]);
            }

            $provider = VideoProvider::from($validated['video_provider']);
            $reference = trim((string) $validated['video_reference']);

            $valid = match ($provider) {
                VideoProvider::YouTube => (bool) preg_match(
                    '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/',
                    $reference,
                ) || (bool) preg_match('/^[A-Za-z0-9_-]{6,}$/', $reference),
                VideoProvider::Vimeo => (bool) preg_match('/(?:vimeo\.com\/)(\d+)/', $reference)
                    || (bool) preg_match('/^\d+$/', $reference),
                VideoProvider::Bunny => (bool) preg_match('/^(?:[A-Za-z0-9-]{8,}|https:\/\/[^\s]+(?:mediadelivery\.net|\.m3u8)[^\s]*)$/', $reference),
                VideoProvider::External => str_starts_with($reference, 'https://'),
            };

            if (! $valid) {
                throw ValidationException::withMessages([
                    'video_reference' => "Enter a valid {$provider->label()} video reference.",
                ]);
            }
        }

        if ($type !== ContentType::Video->value) {
            $validated['video_provider'] = null;
            $validated['video_reference'] = null;
            $validated['video_duration_seconds'] = null;
        }

        if ($type !== ContentType::Article->value) {
            $validated['body'] = null;
        }

        if ($type !== ContentType::Quiz->value) {
            $validated['is_free_preview'] = false;
        }

        return $validated;
    }
}
