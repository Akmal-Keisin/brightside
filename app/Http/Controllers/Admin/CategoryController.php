<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/categories/Index', [
            'categories' => Category::query()
                ->ordered()
                ->withCount('courses')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'color' => $category->color,
                    'position' => $category->position,
                    'courses_count' => $category->courses_count,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category = Category::create([
            ...$validated,
            'position' => (int) Category::query()->max('position') + 1,
        ]);

        $this->auditLogger->log(action: 'category.created', subject: $category, newValues: $category->only(['name']));

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->with(
                'error',
                'This category still has courses. Reassign them before deleting it.',
            );
        }

        $this->auditLogger->log(action: 'category.deleted', subject: $category, oldValues: $category->only(['name']));

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
