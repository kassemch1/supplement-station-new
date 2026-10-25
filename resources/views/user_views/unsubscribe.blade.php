@extends('layouts.store')

@section('title', 'Newsletter')
@section('robots', 'noindex, nofollow')
@section('shell', 'app')

@section('content')
<main class="ss-msgpage">
    <div class="ss-msgpage__card">
        <span class="ss-msgpage__icon">{!! \App\Support\Icons::ui($done ? 'done' : 'mail') !!}</span>
        @if($done)
            <h1>You are unsubscribed</h1>
            <p>{{ $subscription->email }} will no longer get our product emails.</p>
            <a href="{{ route('shop') }}" class="ss-btn ss-btn--red ss-btn--lg">Back to the shop</a>
        @else
            <h1>Unsubscribe from our emails?</h1>
            <p>{{ $subscription->email }} will stop getting new product emails from Supplement Station.</p>
            {{-- Same signed address; a button (not the link itself) so mail scanners opening links can't unsubscribe anyone --}}
            <form method="post" action="{{ request()->fullUrl() }}">
                <button type="submit" class="ss-btn ss-btn--dark ss-btn--lg">Unsubscribe</button>
            </form>
        @endif
    </div>
</main>
@endsection
