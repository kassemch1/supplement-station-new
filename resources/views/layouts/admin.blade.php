{{--
    Layout of every admin page (header, sidebar, footer, the theme's CSS and JS). A page sets:
      @section('title')    the browser tab title
      @section('content')  what goes inside .content-body
      @push('styles')      optional <style> / <link> for this page
      @push('scripts')     optional page scripts; jQuery, Bootstrap, Select2 and the admin scripts are loaded before
    AJAX calls send the CSRF token automatically (resources/assets/admin/admin-base.js).
    The CSS and JS are bundles built by `php artisan assets:build` (config/assets.php): admin.css is the
    template's CSS cut down to what the pages use, admin-theme.css the look (dark by default, light with
    the header switch), admin.js jQuery, Bootstrap, Select2 and resources/assets/admin (menu, switch,
    lists without reloads, the new-order bell).
--}}
<!doctype html>
<html class="no-js" lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', 'Admin') | Supplement Station</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- the store's logo in the browser tab, like the shop --}}
    <link rel="icon" href="{{ asset('assets/img/logo/preloader2.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo/preloader2.png') }}">
    {{-- "Add to Home Screen" makes the admin an app, which is what lets an iPhone show new-order notifications --}}
    <link rel="manifest" href="{{ asset('admin-manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SS Admin">
    <meta name="theme-color" content="#202334">
    {{-- before anything paints, so a light-mode admin never sees a dark flash --}}
    <script>try { if (localStorage.getItem('admin-theme') === 'light') document.documentElement.setAttribute('data-theme', 'light'); } catch (e) {}</script>

    <link rel="stylesheet" href="{{ \App\Services\Assets::url('admin.css') }}">
    {{-- the Inter font does not hold the page up: the system font shows until it has loaded --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    @stack('styles')
    {{-- the admin look; loaded after the page styles so it wins --}}
    <link rel="stylesheet" href="{{ \App\Services\Assets::url('admin-theme.css') }}">
    {{-- Pointing at an admin link for a moment loads that page in the background, so it opens at once
         (Chrome and Edge; other browsers just open it as before). Downloads are left out. --}}
    <script type="speculationrules">
    {"prerender": [{"where": {"and": [
        {"or": [{"href_matches": "/admin"}, {"href_matches": "/admin/*"}, {"href_matches": "/manage*"}]},
        {"not": {"href_matches": "/admin/subscribers/export*"}},
        {"not": {"selector_matches": "[download], [target=_blank], [data-no-prerender]"}}
    ]}, "eagerness": "moderate"}]}
    </script>
</head>

<body data-pending-url="{{ route('admin.orders.pendingSummary') }}" data-pending-list="{{ route('admin.orders.pending') }}"
      data-push-key="{{ route('admin.push.key') }}" data-push-subscribe="{{ route('admin.push.subscribe') }}" data-push-unsubscribe="{{ route('admin.push.unsubscribe') }}">

<div class="main-wrapper">
    @include('partials.adminHeader')
    @include('partials.adminSideBar')

    <div class="content-body">
        {{-- filled by admin-ui.js while orders are waiting --}}
        <div class="ad-pending-bar" id="adPendingBar" hidden>
            <span class="ad-pending-bar__icon">!</span>
            <span class="ad-pending-bar__text" id="adPendingBarText"></span>
            <a href="{{ route('admin.orders.pending') }}" class="ad-pending-bar__link">Open pending orders</a>
        </div>
        @yield('content')
    </div>

    @include('partials.adminFooter')
</div>

<div class="ad-toasts" id="adToasts" aria-live="polite"></div>

<script src="{{ \App\Services\Assets::url('admin.js') }}"></script>
@stack('scripts')
</body>
</html>
