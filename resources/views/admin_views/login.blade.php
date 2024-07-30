<!DOCTYPE html>
<html lang="en">


<!-- Mirrored from www.ansonika.com/betler/login-10.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 26 May 2024 12:29:41 GMT -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Betler Multipurpose Forms HTML Template">
    <meta name="author" content="Ansonika">
    <title>Admin | Login </title>

    <!-- Favicons-->
    {{--    <link rel="shortcut icon" href={{asset("login_assets/img/favicon.ico")}} type="image/x-icon">--}}
    {{--    <link rel="apple-touch-icon" type="image/x-icon" href={{asset("login_assets/img/apple-touch-icon-57x57-precomposed.png")}}>--}}
    {{--    <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href={{asset("login_assets/img/apple-touch-icon-72x72-precomposed.png")}}>--}}
    {{--    <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href={{asset("login_assets/img/apple-touch-icon-114x114-precomposed.png")}}>--}}
    {{--    <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href={{asset("login_assets/img/apple-touch-icon-144x144-precomposed.png")}}>--}}
    <link rel="shortcut icon" href={{asset("assets/icon/FaviconTawwer.png")}}>
    <link rel="apple-touch-icon-precomposed" href={{asset("assets/icon/FaviconTawwer.png")}}>
    <!-- GOOGLE WEB FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">

    <!-- BASE CSS -->
    <link href="login_assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="login_assets/css/vendors.css" rel="stylesheet">
    <link href={{asset("login_assets/css/style.css")}} rel="stylesheet">
    <!-- YOUR CUSTOM CSS -->
    <link href={{asset("login_assets/css/custom.css" )}}rel="stylesheet">
</head>

<body>

<div id="preloader">
    <div data-loader="circle-side"></div>
</div>
<!-- /Preload -->

<div>
    <div class="min-vh-100 d-flex flex-column opacity-mask" data-opacity-mask="rgba(0, 0, 0, 0.05)">
        <div class="container my-auto">
            <div class="row">
                <div class="col-md-9 col-lg-7 col-xl-5 mx-auto my-4">
                    <div class="panel center">
                        <figure>
                            <a href="#"><img src={{asset("login_assets/img/supplement_station_logo.png")}} width="100" height="100" alt=""></a>
                        </figure>

                        <form class="input_style_1" method="post" action="{{route('auth')}}">
                            @csrf
                            <div class="form-group">
                                <label for="email_address">Email Address</label>
                                <input type="email" name="email" id="email_address" class="form-control">
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control">
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                </div>
                            @endif
                            {{--                            <div class="clearfix mb-3">--}}
                            {{--                                <div class="float-start">--}}
                            {{--                                    <label class="container_check">Remember Me--}}
                            {{--                                        <input type="checkbox">--}}
                            {{--                                        <span class="checkmark"></span>--}}
                            {{--                                    </label>--}}
                            {{--                                </div>--}}
                            {{--                                <div class="float-end">--}}
                            {{--                                    <a id="forgot" href="javascript:void(0);">Forgot Password?</a>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                            <button type="submit" class="btn_1 full-width" style="background-color: #743031">Login</button>
                        </form>
                        {{--                        <p class="text-center mt-3 mb-0">Don't have an account? <a href="#0">Sign Up</a></p>--}}
                        {{--                        <form class="input_style_1" method="post">--}}
                        {{--                            <div id="forgot_pw">--}}
                        {{--                                <div class="form-group">--}}
                        {{--                                    <label for="email_forgot">Login email</label>--}}
                        {{--                                    <input type="email" class="form-control" name="email_forgot" id="email_forgot">--}}
                        {{--                                </div>--}}
                        {{--                                <p>You will receive an email containing a link allowing you to reset your password to a new preferred one.</p>--}}
                        {{--                                <div class="text-center"><input type="submit" value="Reset Password" class="btn_1"></div>--}}
                        {{--                            </div>--}}
                        {{--                        </form>--}}

                    </div>
                </div>
                <!-- /col -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
        <div class="container py-3 copy">© Supplements Station - All Rights Reserved.<br>Powered by Tawwer.</div>
{{--        <div class="container py-3 copy">Powered by Tawwer.</div>--}}
    </div>
    <!-- /opacity-mask -->
</div>

<!-- COMMON SCRIPTS -->
<script src={{asset("login_assets/js/common_scripts.js")}}></script>
<script src={{asset("login_assets/js/common_func.js")}}></script>

</body>

<!-- Mirrored from www.ansonika.com/betler/login-10.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 26 May 2024 12:29:43 GMT -->
</html>
