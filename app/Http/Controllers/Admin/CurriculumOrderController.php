<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumOrderController extends Controller
{
    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'chapters' => ['array'],
            'chapters.*.id' => ['required', 'integer'],
            'chapters.*.position' => ['required', 'integer', 'min:0'],
            'contents' => ['array'],
            'contents.*.id' => ['required', 'integer'],
            'contents.*.position' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($course, $validated): void {
            $chapterIds = $course->chapters()->pluck('id')->all();

            foreach ($validated['chapters'] ?? [] as $row) {
                if (in_array($row['id'], $chapterIds, true)) {
                    $course->chapters()->whereKey($row['id'])->update(['position' => $row['position']]);
                }
            }

            $contentIds = $course->contents()->pluck('contents.id')->all();

            foreach ($validated['contents'] ?? [] as $row) {
                if (in_array($row['id'], $contentIds, true)) {
                    $course->contents()->whereKey($row['id'])->update(['position' => $row['position']]);
                }
            }
        });

        return back()->with('success', 'Curriculum order saved.');
    }
}
