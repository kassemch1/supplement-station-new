{{-- Under the products: the admin's "About" text for this category, or a short generated line.
     Swapped together with the products when the shop moves to another category without reloading. --}}
@php
    $about = $category?->description ? preg_split('/\R\s*\R/u', trim($category->description)) : [];
@endphp
@if(!$search && $products->currentPage() === 1)
    @if($about)
        <section class="ss-wrap ss-shop__about">
            <h2>About {{ $category->name }}</h2>
            @foreach($about as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </section>
    @elseif($category)
        <p class="ss-wrap ss-shop__intro">Shop {{ $category->name }} in Lebanon at Supplement Station: {{ $totalProducts }} {{ \Illuminate\Support\Str::plural('product', $totalProducts) }}, with prices in USD. Order online or visit our store in Beirut.</p>
    @endif
@endif