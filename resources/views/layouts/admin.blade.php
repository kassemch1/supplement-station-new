{{--
    Layout of every admin page (header, sidebar, footer, the theme's CSS and JS). A page sets:
      @section('title')    the browser tab title
      @section('content')  what goes inside .content-body
      @push('styles')      optional <style> / <link> for this page
      @push('scripts')     optional page scripts; jQuery, Bootstrap and the theme script are loaded before
    AJAX calls send the CSRF token automatically (see the $.ajaxSetup below).
--}}
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', 'Admin') | Supplement Station</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/cryptocurrency-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/helper.css') }}">
    {{-- style.css is style-primary.css plus the logo size; the pages used to load both --}}
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
    @stack('styles')
</head>

<body class="skin-dark">

<div class="main-wrapper">
    @include('partials.adminHeader')
    @include('partials.adminSideBar')

    <div class="content-body">
        @yield('content')
    </div>

    @include('partials.adminFooter')
</div>

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
@stack('scripts')
</body>
</html>
