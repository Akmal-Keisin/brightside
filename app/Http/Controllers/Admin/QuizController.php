<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Quiz;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function update(Request $request, Content $content): RedirectResponse
    {
        if (! $content->isQuiz()) {
            abort(404);
        }

        $validated = $request->validate([
            'passing_score' => ['required', 'integer', 'min:1', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:50'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:60', 'max:86400'],
            'shuffle_questions' => ['boolean'],
            'show_answers_after_submit' => ['boolean'],
            'questions' => ['array'],
            'questions.*.question' => ['required', 'string', 'max:2000'],
            'questions.*.explanation' => ['nullable', 'string', 'max:2000'],
            'questions.*.points' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.options.*.label' => ['required', 'string', 'max:500'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ]);

        foreach ($validated['questions'] ?? [] as $index => $question) {
            $correct = collect($question['options'])->where('is_correct', true)->count();

            if ($correct !== 1) {
                throw ValidationException::withMessages([
                    "questions.{$index}.options" => 'Each question needs exactly one correct answer.',
                ]);
            }
        }

        DB::transaction(function () use ($content, $validated): void {
            /** @var Quiz $quiz */
            $quiz = $content->quiz()->firstOrCreate([], [
                'passing_score' => 70,
                'show_answers_after_submit' => true,
            ]);

            $quiz->update([
                'passing_score' => $validated['passing_score'],
                'max_attempts' => $validated['max_attempts'] ?? null,
                'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
                'shuffle_questions' => request()->boolean('shuffle_questions'),
                'show_answers_after_submit' => request()->boolean('show_answers_after_submit'),
            ]);

            $quiz->questions()->delete();

            foreach (array_values($validated['questions'] ?? []) as $position => $question) {
                $created = $quiz->questions()->create([
                    'question' => $question['question'],
                    'explanation' => $question['explanation'] ?? null,
                    'points' => $question['points'],
                    'position' => $position + 1,
                ]);

                foreach (array_values($question['options']) as $optionPosition => $option) {
                    $created->options()->create([
                        'label' => $option['label'],
                        'is_correct' => (bool) ($option['is_correct'] ?? false),
                        'position' => $optionPosition + 1,
                    ]);
                }
            }
        });

        $this->auditLogger->log(
            action: 'quiz.updated',
            subject: $content,
            newValues: ['questions' => count($validated['questions'] ?? [])],
        );

        return back()->with('success', 'Quiz saved.');
    }
}
