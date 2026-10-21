<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MarkdownRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkdownPreviewController extends Controller
{
    public function __invoke(Request $request, MarkdownRenderer $renderer): JsonResponse
    {
        $validated = $request->validate([
            'markdown' => ['nullable', 'string', 'max:50000'],
        ]);

        return response()->json([
            'html' => $renderer->render($validated['markdown'] ?? ''),
        ]);
    }
}
