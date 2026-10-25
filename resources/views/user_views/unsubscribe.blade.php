@extends('layouts.store')

@section('title', 'Newsletter')
@section('robots', 'noindex, nofollow')

@section('content')
    <main>
        <section class="pt-120 pb-120">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        @if($done)
                            <h1 class="mb-20" style="font-size: 32px;">You are unsubscribed</h1>
                            <p class="mb-30">{{ $subscription->email }} will no longer get our product emails.</p>
                            <a href="{{ route('shop') }}" class="thm-btn">Back to the shop</a>
                        @else
                            <h1 class="mb-20" style="font-size: 32px;">Unsubscribe from our emails?</h1>
                            <p class="mb-30">{{ $subscription->email }} will stop getting new product emails from Supplement Station.</p>
                            {{-- Same signed address; a button (not the link itself) so mail scanners opening links can't unsubscribe anyone --}}
                            <form method="post" action="{{ request()->fullUrl() }}">
                                <button type="submit" class="thm-btn">Unsubscribe</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
