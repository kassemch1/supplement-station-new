{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>{{ url('/') }}/</loc>
        @if($lastUpdate)<lastmod>{{ $lastUpdate->toAtomString() }}</lastmod>@endif
    </url>
    <url>
        <loc>{{ route('home') }}</loc>
        @if($lastUpdate)<lastmod>{{ $lastUpdate->toAtomString() }}</lastmod>@endif
    </url>
@foreach($categories as $category)
    <url>
        <loc>{{ $category->url() }}</loc>
        @if($categoryUpdated[$category->id])<lastmod>{{ \Illuminate\Support\Carbon::parse($categoryUpdated[$category->id])->toAtomString() }}</lastmod>@endif
    </url>
@endforeach
@foreach($products as $product)
    <url>
        <loc>{{ $product->url() }}</loc>
        @if($product->updated_at)<lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>@endif
        @foreach($product->images->take(10) as $image)
        <image:image>
            <image:loc>{{ asset($image->url) }}</image:loc>
        </image:image>
        @endforeach
    </url>
@endforeach
</urlset>
