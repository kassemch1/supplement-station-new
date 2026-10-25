@extends('layouts.store')

@php
    $intro = $category
        ? "Shop {$category->name} in Lebanon at Supplement Station: {$totalProducts} " . \Illuminate\Support\Str::plural('product', $totalProducts) . ', with prices in USD. Order online or visit our store in Beirut.'
        : null;
@endphp
@section('title', $pageTitle)
@section('description', $category
    ? \App\Support\Seo::text("Buy {$category->name} in Lebanon at Supplement Station, Beirut. {$totalProducts} " . \Illuminate\Support\Str::plural('product', $totalProducts) . ' from trusted brands. Order online.')
    : 'Shop protein, creatine, mass gainers, pre-workouts, amino acids, vitamins, fat burners and gym gear in Lebanon. Order online at Supplement Station, Beirut.')
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
        \App\Support\Seo::collection($heading, $canonical, $products->items(), $startIndex),
        \App\Support\Seo::breadcrumbs(array_values(array_filter([
            ['Home', route('home')],
            ['Shop', route('shop')],
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

    @if($intro && !$search && $products->currentPage() === 1)
        <p class="ss-wrap ss-shop__intro">{{ $intro }}</p>
    @endif
</main>
<div class="ss-sheet-backdrop" data-close-filters></div>
@endsection
