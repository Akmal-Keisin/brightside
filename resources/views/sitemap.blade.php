<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('courses.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('legal.terms') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>{{ route('legal.privacy') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>{{ route('legal.refund') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    @foreach ($courses as $course)
        <url>
            <loc>{{ route('courses.show', $course) }}</loc>
            <lastmod>{{ $course->updated_at?->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
