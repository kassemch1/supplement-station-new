@extends('layouts.store')

@section('title', $pageTitle)
{{-- The whole shop is the front page (/): it carries the store's name and description --}}
@if($frontPage)
    @section('full_title', 'Supplement Station Lebanon | Protein, Creatine & Gym Supplements')
@endif
@section('description', $frontPage
    ? 'Supplement Station in Beirut, Lebanon: whey protein, creatine, mass gainers, pre-workouts, vitamins and gym gear from Optimum Nutrition, MuscleTech, BPI and more. Order online.'
    : ($category
    ? \App\Support\Seo::text($category->description ?: "Buy {$category->name} in Lebanon at Supplement Station, Beirut. {$totalProducts} " . \Illuminate\Support\Str::plural('product', $totalProducts) . ' from trusted brands. Order online.')
    : 'Shop protein, creatine, mass gainers, pre-workouts, amino acids, vitamins, fat burners and gym gear in Lebanon. Order online at Supplement Station, Beirut.'))
@section('canonical', $canonical)
@if($noindex)
    @section('robots', 'noindex, follow')
@endif
@push('head')
    @if($prevUrl)<link rel="prev" href="{{ $prevUrl }}">@endif
    @if($nextUrl)<link rel="next" href="{{ $nextUrl }}">@endif
@endpush
@push('schema')
    {{ \App\Support\Seo::script([
        ...($frontPage ? [\App\Support\Seo::store(), \App\Support\Seo::website()] : []),
        \App\Support\Seo::collection($heading, $canonical, $products->items(), $startIndex),
        \App\Support\Seo::breadcrumbs(array_values(array_filter([
            ['Shop', url('/') . '/'],
            $main && $main->id !== $category?->id ? [$main->name, $main->url()] : null,
            $category ? [$category->name, $category->url()] : null,
        ]))),
    ]) }}
@endpush
@section('page', 'shop')
@section('shell', 'app')

@section('content')
@include('partials.subscription-modal', ['delay' => 3000])
<main class="ss-shop" id="shop-app">
    <div id="shop-nav">
        @include('partials.shop.nav')
    </div>

    <div class="ss-wrap ss-shop__body">
        <aside class="ss-sidebar" id="shop-sidebar" aria-label="Filters">
            @include('partials.shop.sidebar')
        </aside>
        <section class="ss-results" id="shop-results" aria-live="polite">
            @include('partials.shop.results')
        </section>
    </div>

    <div id="shop-about">
        @include('partials.shop.about')
    </div>
</main>
<div class="ss-sheet-backdrop" data-close-filters></div>
@endsection
