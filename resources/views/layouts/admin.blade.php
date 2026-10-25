{{--
    Layout of every admin page (header, sidebar, footer, the theme's CSS and JS). A page sets:
      @section('title')    the browser tab title
      @section('content')  what goes inside .content-body
      @push('styles')      optional <style> / <link> for this page
      @push('scripts')     optional page scripts; jQuery, Bootstrap and the theme script are loaded before
    AJAX calls send the CSRF token automatically (see the $.ajaxSetup below).
    admin-theme.css is the look (dark by default, light with the header switch), admin-ui.js the
    menu, the switch and the new-order bell.
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
    {{-- before anything paints, so a light-mode admin never sees a dark flash --}}
    <script>try { if (localStorage.getItem('admin-theme') === 'light') document.documentElement.setAttribute('data-theme', 'light'); } catch (e) {}</script>

    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/cryptocurrency-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/helper.css') }}">
    {{-- style.css is style-primary.css plus the logo size; the pages used to load both --}}
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    @stack('styles')
    {{-- the admin look; loaded after the page styles so it wins --}}
    <link rel="stylesheet" href="{{ asset('admin_assets/css/admin-theme.css') }}?v={{ filemtime(public_path('admin_assets/css/admin-theme.css')) }}">
</head>

<body data-pending-url="{{ route('admin.orders.pendingSummary') }}" data-pending-list="{{ route('admin.orders.pending') }}">

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

<script src="{{ asset('admin_assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/popper.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/tippy4.min.js.js') }}"></script>
<script src="{{ asset('admin_assets/js/main.js') }}"></script>
<script>
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
</script>
<script src="{{ asset('admin_assets/js/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/admin-ui.js') }}?v={{ filemtime(public_path('admin_assets/js/admin-ui.js')) }}"></script>
@stack('scripts')
</body>
</html>
