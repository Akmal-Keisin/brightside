<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function (): string {
            $courses = Course::query()
                ->published()
                ->select(['slug', 'updated_at'])
                ->latest('updated_at')
                ->get();

            return view('sitemap', ['courses' => $courses])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
