{{-- Page not found (an old link, a typo, a product that was removed) --}}
@extends('layouts.store')

@section('title', 'Page not found')
@section('robots', 'noindex, follow')
@section('shell', 'app')

@section('content')
<main class="ss-msgpage">
    <div class="ss-msgpage__card">
        <span class="ss-msgpage__icon">{!! \App\Support\Icons::ui('search') !!}</span>
        <h1>We could not find this page</h1>
        <p>The link may be old or the product may no longer be in the shop. Search for it or browse the shop.</p>
        <form action="{{ route('shop') }}" method="get" role="search" class="ss-msgpage__search">
            <input class="ss-input" type="search" name="q" placeholder="Search protein, creatine, brands…" aria-label="Search products">
            <button type="submit" class="ss-btn ss-btn--dark">Search</button>
        </form>
        <a href="{{ route('shop') }}" class="ss-btn ss-btn--red ss-btn--lg">Go to the shop</a>
    </div>
</main>
@endsection
