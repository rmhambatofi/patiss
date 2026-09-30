{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ url('/') }}</loc></url>
    <url><loc>{{ url('/gateaux') }}</loc></url>
@foreach ($types as $type)
    <url>
        <loc>{{ route('collection.show', $type) }}</loc>
        <lastmod>{{ $type->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
</urlset>
