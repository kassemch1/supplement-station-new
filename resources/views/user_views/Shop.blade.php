<!doctype html>
<html lang="en">
<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Shop | Supplement Station</title>

    <link rel="shortcut icon" href={{ asset('assets/img/logo/preloader2.png')}} type="images/x-icon"/>
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/logo/preloader2.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/img/logo/preloader2.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/img/logo/preloader2.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/logo/preloader2.png') }}">

    <!-- css include -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    <style>
        /* Styling for the sale label */
        .sale-label {
            background-color: #A02334; /* Background color for the label */
            color: white; /* Text color */
            padding: 5px 10px; /* Padding around the text */
            font-weight: bold; /* Bold text */
            position: absolute; /* Absolute positioning */
            top: 10px; /* Position it at the top */
            left: 10px; /* Adjust as needed */
            z-index: 10; /* Ensure it appears above the image */
            border-radius: 3px; /* Rounded corners */
            text-transform: uppercase; /* Uppercase text */
            font-size: 14px; /* Font size */
        }

        .xb-item--img {
            position: relative; /* Needed for absolute positioning of the label */
            display: inline-block; /* Ensure the container fits around the image */
        }

        .xb-item--cart-btn.disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .no-stock img {
            pointer-events: none;
        }

        .no-stock-title {
            color: gray;
            cursor: default;
        }

        /* Customize the overall pagination container */
        .pagination {
            display: flex;
            justify-content: center;
            padding: 0;
            margin: 0;
        }

        /* Style for pagination items */
        .pagination .page-item {
            margin: 0 2px; /* Spacing between buttons */
        }

        /* Style for pagination links */
        .pagination .page-link {
            color: #ffffff; /* Text color */
            background-color: #A02334; /* Background color */
            border: 1px solid #A02334; /* Border color */
        }

        /* Style for active pagination link */
        .pagination .page-item.active .page-link {
            background-color: #fff; /* Active background color */
            color: #A02334;
            border-color: #A02334; /* Active border color */
        }

        /* Style for disabled pagination link */
        .pagination .page-item.disabled .page-link {
            color: #6c757d; /* Disabled text color */
            background-color: #e9ecef; /* Disabled background color */
            border-color: #e9ecef; /* Disabled border color */
        }

        /* Hover effect for pagination links */
        .pagination .page-link:hover {
            background-color: #fff; /* Hover background color */
            border-color: #fff; /* Hover border color */
            color: #A02334;
        }
        /* Subscribe modal — refreshed */
        .subscription-modal { position: fixed; inset: 0; z-index: 9999; }
        .subscription-modal-overlay {
            position: absolute; inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: flex; justify-content: center; align-items: center;
            padding: 20px;
        }
        .subscription-modal-content {
            background: #fff;
            border-radius: 18px;
            padding: 0;
            max-width: 440px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
            animation: modalSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: center;
        }
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(20px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .subscription-modal-header {
            position: relative;
            padding: 36px 28px 0;
            background: transparent;
            color: inherit;
            border: none;
            display: block;
        }
        .subscription-modal-icon {
            display: inline-flex;
            align-items: center; justify-content: center;
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #A02334 0%, #d63b50 100%);
            color: #fff;
            font-size: 26px;
            box-shadow: 0 10px 24px rgba(160, 35, 52, 0.35);
            margin-bottom: 18px;
        }
        .subscription-modal-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.2px;
        }
        .close-modal {
            position: absolute;
            top: 14px; right: 14px;
            background: rgba(15, 23, 42, 0.06);
            border: none;
            color: #64748b;
            font-size: 18px; line-height: 1;
            width: 32px; height: 32px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background .2s ease, color .2s ease;
        }
        .close-modal:hover { background: rgba(15, 23, 42, 0.12); color: #0f172a; opacity: 1; }

        .subscription-modal-body {
            padding: 18px 32px 32px;
        }
        .subscription-modal-body p {
            margin: 0 0 22px;
            color: #475569;
            text-align: center;
            font-size: 14.5px;
            line-height: 1.55;
        }

        .form-group { margin-bottom: 14px; position: relative; }
        .form-group .input-icon {
            position: absolute;
            top: 50%; left: 16px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14.5px;
            color: #0f172a;
            background: #f8fafc;
            box-sizing: border-box;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .form-group input::placeholder { color: #94a3b8; }
        .form-group input:focus {
            outline: none;
            border-color: #A02334;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(160, 35, 52, 0.12);
        }

        .form-actions {
            display: flex; flex-direction: column; gap: 8px;
            margin-top: 4px;
        }
        .btn-subscribe, .btn-cancel {
            padding: 13px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14.5px;
            font-weight: 600;
            transition: transform .15s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
            width: 100%;
            font-family: inherit;
        }
        .btn-subscribe {
            background: linear-gradient(135deg, #A02334 0%, #d63b50 100%);
            color: #fff;
            box-shadow: 0 6px 16px rgba(160, 35, 52, 0.28);
        }
        .btn-subscribe:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(160, 35, 52, 0.38); }
        .btn-subscribe:active { transform: translateY(0); }
        .btn-cancel {
            background: transparent;
            color: #64748b;
            box-shadow: none;
            padding: 10px 18px;
            font-weight: 500;
        }
        .btn-cancel:hover { color: #0f172a; background: #f1f5f9; }

        @media (max-width: 480px) {
            .subscription-modal-content { border-radius: 16px; }
            .subscription-modal-header { padding: 28px 22px 0; }
            .subscription-modal-body { padding: 16px 22px 24px; }
            .subscription-modal-header h3 { font-size: 20px; }
        }

    </style>
</head>

<body>

<!-- backtotop - start -->
<div class="xb-backtotop">
    <a href="#" class="scroll">
        <i class="far fa-arrow-up"></i>
    </a>
</div>
<!-- backtotop - end -->
@include('partials/navBar',['categories'=>$categories])
<!-- header end -->

<!-- sidebar-info end -->

<div class="body-overlay"></div>
<!-- Subscription Modal -->
<div id="subscriptionModal" class="subscription-modal" style="display: none;">
    <div class="subscription-modal-overlay">
        <div class="subscription-modal-content">
            <div class="subscription-modal-header">
                <span class="subscription-modal-icon"><i class="fas fa-dumbbell"></i></span>
                <h3>Stay Strong, Stay Updated!</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="subscription-modal-body">
                <p>Stay updated on the latest deals, exclusive offers, and new product alerts delivered to your house!</p>
                <form id="subscriptionForm">
                    <div class="form-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="subscriptionEmail" name="email" placeholder="Enter your email address" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-subscribe">Subscribe</button>
                        <button type="button" class="btn-cancel" id="cancelSubscription">No, Thanks</button>
                    </div>
                </form>
                <div id="subscriptionMessage" style="display: none; margin-top: 10px;"></div>
            </div>
        </div>
    </div>
</div>
<!-- main area start  -->
<main>
    <!-- breadcrumb start -->
    <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.webp')}}">
        <div class="container">
            <div class="breadcrumb__content text-center">
                <h2 class="breadcrumb__title" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">Shop</h2>
                <ul class="breadcrumb__list clearfix">
                    <li class="breadcrumb-item">
                        <a href="{{route('home')}}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                            Supplement Station
                        </a>
                    </li>
                    <li class="breadcrumb-item" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                        Shop
                    </li>
                </ul>
            </div>
        </div>
    </section>



    <!-- breadcrumb end -->

    <!-- shop start -->
    <section class="shop pt-115 pb-385">
        <div class="container">
            <div class="row mt-none-60">
                <div class="col-lg-3 mt-60">
                    <div class="shop-sidebar sidebar-area mt-none-40">

                        <div class="widget mt-40">
                            <h2 class="widget__title">Search</h2>
                            <div class="widget__inner">
                                <form id="search-form" class="widget__search" method="GET" action="{{route('shop')}}">
                                    @csrf
                                    <input type="text" name="shop-search" placeholder="Search..." id="search-input">
                                    <button type="submit"><i class="far fa-search"></i></button>
                                </form>
                            </div>
                        </div>


                        <div class="widget mt-40">
                            <h2 class="widget__title">
                                <span>Product Categories</span>
                            </h2>
                            @if($agent->isDesktop() || $agent->isTablet())
                                <div class="widget__inner"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                        <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form">
                                                    @csrf
                                                    <input type="hidden" name="category" value="show-all">
                                                    <button type="submit" class="btn btn-link category-button" style="text-decoration: none;color: inherit">
                                                        Show All
                                                    </button>

                                                </form>
                                        </li>
                                        @foreach($categories as $category)
                                            <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form">
                                                    @csrf
                                                    <input type="hidden" name="category" value="{{ $category->name }}">
                                                    <button type="submit" class="btn btn-link category-button" style="text-decoration: none;color: inherit">
                                                        {{ $category->name }}
                                                    </button>

                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif($agent->isMobile())
                                <div class="widget__inner"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                            <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form-phone">
                                                    @csrf
                                                    <select name="category-phone" id="category-phone">
                                                        <option value="all" >Show All</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{$category->name}}" {{ $currentCategoryId === $category->id ? 'selected' : '' }}>{{$category->name}}</option>
                                                        @endforeach
                                                    </select>

                                                </form>
                                            </li>
                                    </ul>
                                </div>
                            @endif


                        </div>
                        @if(!$agent->isMobile())
                        <!--Start Of the Offers Section -->
                        @if($offersProducts)
                            <div class="widget mt-40">
                                <h2 class="widget__title">Our Best Offers</h2>
                                <div class="widget__inner">
                                    <ul class="widget-product">
                                        @foreach($offersProducts as $offer)
                                            <li class="widget-product__item">
                                                <div class="thumb">
                                                    <a href="{{ route('products.show', ['id' => $offer->id]) }}">
                                                        @if($offer->images->isNotEmpty())
                                                            <img src="{{ asset($offer->images->first()->url) }}" alt="" loading="lazy">
                                                        @else
                                                            No image available
                                                        @endif
                                                    </a>
                                                </div>
                                                <div class="content">
                                                    <h3>
                                                        <a href="{{ route('products.show', ['id' => $offer->id]) }}">{{ $offer->name }}</a>
                                                    </h3>
                                                    <span class="price">
                                                        @if($offer->discount > 0)
                                                            <span
                                                                style="text-decoration: line-through; color: gray;">${{ number_format($offer->price, 2) }}</span>
                                                            <span
                                                                style="color: #A02334;">${{ number_format($offer->price - ($offer->price * $offer->discount / 100), 2) }}</span>
                                                        @else
                                                            ${{ number_format($offer->price, 2) }}
                                                        @endif
                                                    </span>

                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @endif

                        <!--End Of the Offers Section -->


                    </div>
                </div>
                <div class="col-lg-9 mt-60">
                    <div class="woocommerce-content-wrap">
                        <div class="woocommerce-toolbar-top ul_li_between">
                            <div id="products_indexing">
                                @include('partials/products_indexing')
                            </div>
                            <div class="woocommerce-toolbar-top-right ul_li">
                                <form id="sort-form" class="woocommerce-ordering" method="get" action="{{route('shop')}}">
                                    @csrf
                                    <input type="hidden" name="category" id="category-input" value="">
                                    <select name="orderby" class="orderby" id="sort-by-price">
                                        <option value="sort" selected="selected">Apply sorting</option>
                                        <option value="default">Default</option>
                                        <option value="low-to-high">Sort by price: low to high</option>
                                        <option value="high-to-low">Sort by price: high to low</option>
                                    </select>
                                    <input type="hidden" name="post_type" value="product">
                                </form>
                            </div>
                        </div>
                        <div class="woocommerce-content-inner">
                            <div class="products" id="product-list">
                                @include('partials/product_list',['product'=>$product,'agent'=>$agent])
                            </div>

                            <div id="pagination">
                                @include('partials/product_pagination',['product'=>$product])
                            </div>


                        </div>
                        <br>
                        <br>
                    </div>
                </div>

                <div class="row mt-none-60">
                    <div class="col-lg-3 mt-60">
                        <div class="shop-sidebar sidebar-area mt-none-40">
                @if($agent->isMobile())
                    <!--Start Of the Offers Section -->
                    @if($offersProducts)
                        <div class="widget mt-40">
                            <h2 class="widget__title">Our Best Offers</h2>
                            <div class="widget__inner">
                                <ul class="widget-product">
                                    @foreach($offersProducts as $offer)
                                        <li class="widget-product__item">
                                            <div class="thumb">
                                                <a href="{{ route('products.show', ['id' => $offer->id]) }}">
                                                    @if($offer->images->isNotEmpty())
                                                        <img src="{{ asset($offer->images->first()->url) }}" alt="" loading="lazy">
                                                    @else
                                                        No image available
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="content">
                                                <h3>
                                                    <a href="{{ route('products.show', ['id' => $offer->id]) }}">{{ $offer->name }}</a>
                                                </h3>
                                                <span class="price">
                                                        @if($offer->discount > 0)
                                                        <span
                                                            style="text-decoration: line-through; color: gray;">${{ number_format($offer->price, 2) }}</span>
                                                        <span
                                                            style="color: #A02334;">${{ number_format($offer->price - ($offer->price * $offer->discount / 100), 2) }}</span>
                                                    @else
                                                        ${{ number_format($offer->price, 2) }}
                                                    @endif
                                                    </span>

                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- shop end -->

</main>
<!-- main area end  -->

<!-- footer strt -->
@include('partials/footer')
<!-- footer end -->


</div>

<!-- jquery include -->
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/swiper.min.js') }}"></script>
<script src="{{ asset('assets/js/wow.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/js/touchspin.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.inview.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.easing.js') }}"></script>
<script src="{{ asset('assets/js/scrollspy.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Subscription Modal Logic
    $(document).ready(function() {
        // Show subscription modal on page load with delay
        setTimeout(function() {
            checkAndShowSubscriptionModal();
        }, 3000); // Show modal after 3 seconds

        // Function to check if subscription modal should be shown
        function checkAndShowSubscriptionModal() {
            $.ajax({
                url: '{{ route('subscription.check.modal') }}',
                method: 'GET',
                success: function(response) {
                    if (response.show_modal) {
                        showSubscriptionModal();
                    }
                },
                error: function() {
                    console.log('Failed to check subscription modal status');
                }
            });
        }

        // Function to show subscription modal
        function showSubscriptionModal() {
            $('#subscriptionModal').fadeIn();

            // Handle modal close events
            const closeEvents = ['#closeModal', '#cancelSubscription'];
            closeEvents.forEach(selector => {
                $(selector).off('click').on('click', function() {
                    handleSubscriptionCancel();
                });
            });

            // Handle subscription form submission
            $('#subscriptionForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                handleSubscriptionSubmit();
            });

            // Close modal when clicking outside
            $('.subscription-modal-overlay').off('click').on('click', function(e) {
                if (e.target === this) {
                    handleSubscriptionCancel();
                }
            });
        }

        // Handle subscription form submission
        function handleSubscriptionSubmit() {
            const email = $('#subscriptionEmail').val();
            const submitButton = $('.btn-subscribe');
            const originalText = submitButton.text();

            submitButton.text('Processing...').prop('disabled', true);

            $.ajax({
                url: '{{ route('subscription.subscribe') }}',
                method: 'POST',
                data: {
                    email: email,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const messageDiv = $('#subscriptionMessage');

                    if (response.success) {
                        messageDiv.html('<div style="color: green;">' + response.message + '</div>').show();
                        setTimeout(() => {
                            $('#subscriptionModal').fadeOut();
                        }, 2000);
                    } else {
                        // Show error message but keep the form open
                        messageDiv.html('<div style="color: red;">' + response.message + '</div>').show();
                        // Clear the email input for the user to try a different email
                        $('#subscriptionEmail').val('').attr('placeholder', 'Try a different email address').focus();
                    }
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    $('#subscriptionMessage').html('<div style="color: red;">' + errorMessage + '</div>').show();
                },
                complete: function() {
                    submitButton.text(originalText).prop('disabled', false);
                }
            });
        }

        // Handle subscription cancellation
        function handleSubscriptionCancel() {
            $.ajax({
                url: '{{ route('subscription.cancel') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                complete: function() {
                    $('#subscriptionModal').fadeOut();
                }
            });
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('#search-form').submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = $(this).serialize(); // Convert form data to a query string

            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                success: function (response) {
                    // Ensure response keys match those sent from the server
                    $('#sort-by-price').val('sort');
                    if (response.productList) {
                        $('#product-list').empty().html(response.productList);
                    } else {
                        $('#product-list').html('<p>No products found.</p>');
                    }

                    if (response.paginatee) { // Corrected the key name
                        $('#pagination').empty().html(response.paginatee);
                    } else {
                        $('#pagination').html('');
                    }
                    if (response.productsIndexing) { // Corrected the key name
                        $('#products_indexing').empty().html(response.productsIndexing);
                    } else {
                        $('#products_indexing').html('');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('An error occurred while processing the request aa.');
                }
            });
        });
    });

    // Handle sort form change
    $('#sort-by-price').change(function () {
        // Submit the form
        $('#sort-form').submit();
    });



    // Handle sort form submission via AJAX
    $('#sort-form').submit(function (e) {
        e.preventDefault(); // Prevent default form submission
        var selectedCategory;

        // Check if the user is on mobile (category-phone select exists)
                if (document.getElementById('category-phone')) {
                    selectedCategory = document.getElementById('category-phone').value;
                }
        // If not mobile, check for desktop forms
                else {
                    var activeForm = document.querySelector('.category-form button.active'); // Assuming you have a way to mark the active button
                    if (activeForm) {
                        selectedCategory = activeForm.closest('form').querySelector('input[name="category"]').value;
                    } else {
                        // Fallback to the first category form's input if no button has been clicked
                        var categoryFormInputs = document.querySelector('.category-form input[name="category"]');
                        if (categoryFormInputs) {
                            selectedCategory = categoryFormInputs.value;
                        }
                    }
                }

        // Update the hidden input in the sort-form with the selected category
                document.getElementById('category-input').value = selectedCategory;

        var formData = $(this).serialize(); // Convert form data to a query string

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).attr('action'), // Use form's action attribute as URL
            data: formData,
            success: function (response) {
                if (response.productList) {
                    $('#product-list').empty().html(response.productList);
                } else {
                    $('#product-list').html('<p>No products found.</p>');
                }

                if (response.paginatee) {
                    $('#pagination').empty().html(response.paginatee);
                } else {
                    $('#pagination').html('');
                }
                if (response.productsIndexing) { // Corrected the key name
                    $('#products_indexing').empty().html(response.productsIndexing);
                } else {
                    $('#products_indexing').html('');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request.');
            }
        });
    });

    // Handle category form submission via AJAX
    $('.category-form').submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Convert form data to a query string
        console.log(formData);  // Log the serialized form data

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).attr('action'), // Use form's action attribute as URL
            data: formData,
            success: function (response) {
                $('#sort-by-price').val('sort');
                if (response.productList) {
                    $('#product-list').empty().html(response.productList);
                } else {
                    $('#product-list').html('<p>No products found.</p>');
                }

                if (response.paginatee) {
                    $('#pagination').empty().html(response.paginatee);
                } else {
                    $('#pagination').html('');
                }
                if (response.productsIndexing) { // Corrected the key name
                    $('#products_indexing').empty().html(response.productsIndexing);
                } else {
                    $('#products_indexing').html('');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request. h');
            }
        });
    });


    // Handle category form change
    $('#category-phone').change(function () {
        $('.category-form-phone').submit(); // Submit the form via AJAX
    });

    // Handle category form submission via AJAX
    $('.category-form-phone').submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Convert form data to a query string

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).attr('action'), // Use form's action attribute as URL
            data: formData,
            success: function (response) {
                $('#sort-by-price').val('sort');
                if (response.productList) {
                    $('#product-list').empty().html(response.productList);
                } else {
                    $('#product-list').html('<p>No products found.</p>');
                }

                if (response.paginatee) {
                    $('#pagination').empty().html(response.paginatee);
                } else {
                    $('#pagination').html('');
                }
                if (response.productsIndexing) { // Corrected the key name
                    $('#products_indexing').empty().html(response.productsIndexing);
                } else {
                    $('#products_indexing').html('');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request.');
            }
        });
    });


</script>

<script>
    $(document).ready(function() {
        // Function to handle pagination clicks
        $(document).on('click', '#pagination-container .pagination a', function(e) {
            e.preventDefault(); // Prevent the default link behavior

            var url = $(this).attr('href'); // Get the URL from the link
            fetchPage(url);
        });

        // Function to fetch page content via AJAX
        function fetchPage(url) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                url: url,
                success: function(response) {
                    // Update the product list and pagination links
                    if (response.productList) {
                        $('#product-list').empty().html(response.productList);
                    } else {
                        $('#product-list').html('<p>No products found.</p>');
                    }

                    if (response.paginatee) {
                        $('#pagination-container').empty().html(response.paginatee);
                    } else {
                        $('#pagination-container').html('');
                    }
                    if (response.productsIndexing) { // Corrected the key name
                        $('#products_indexing').empty().html(response.productsIndexing);
                    } else {
                        $('#products_indexing').html('');
                    }

                    // Scroll to the top of the products
                    $('html, body').animate({
                        scrollTop: $('#product-list').offset().top
                    }, 500);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('An error occurred while processing the request.');
                }
            });
        }
    });

</script>


<!--minicart-->
<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function (response) {
                console.log("response", response.items); // Check response structure

                if (!response.items || !Array.isArray(response.items)) {
                    console.error("Invalid response structure");
                    return;
                }

                $('.header-mini-cart').empty(); // Clear previous items
                let total = 0;

                if (response.items.length === 0) {
                    $('.header-mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        if (!item.product || !item.product.price || !item.product.name) {
                            console.error("Invalid item structure");
                            return;
                        }

                        const discount = item.product.discount || 0; // Get discount percentage
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount
                        const itemTotal = discountedPrice * item.quantity; // Calculate total for this item

                        $('.header-mini-cart').append(`
                            <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
                                <div class="mini-cart-img" style="margin-right: 10px;">
                                    <img src="${item.product_image || 'path/to/default/image.jpg'}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="mini-cart-content" style="flex-grow: 1;">
                                    <h4 class="product-title" style="margin: 0; font-size: 14px;">
                                        <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>
                                    </h4>
                                    <div class="mini-cart-price" style="margin-top: 5px;">
                                        ${item.quantity} ×
                                        <span class="woocommerce-Price-amount amount" style="color: red;">$${discountedPrice.toFixed(2)}</span>

                                    </div>
                                </div>
                                <div class="remove-button" style="margin-left: auto;">
                                    <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
                                </div>
                            </div>
                        `);

                        total += itemTotal; // Sum the total amount
                    });

                    $('.header-mini-cart').append(`
                        <p class="woocommerce-mini-cart__total">
                            <strong>Subtotal:</strong>
                            <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>
                        </p>
                        <p class="checkout-link">
                            <a href="/viewCart" class="button wc-forward">View cart</a>
                            <a href="/Checkout" class="button checkout wc-forward">Checkout</a>
                        </p>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function () {
        $('.header-shop-cart a').on('click', function () {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });

    // Remove item from cart
    $(document).on('click', '.remove', function (event) {
        event.preventDefault(); // Prevent the default link behavior

        const productId = $(this).data('product_id');

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                // alert(response.message);
                fetchCart(); // Refresh the cart items
            },
            error: function (xhr, status, error) {
                alert('Failed to remove item from cart.');
            }
        });
    });
</script>



<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function (response) {
                console.log("response", response.items); // Check response structure

                if (!response.items || !Array.isArray(response.items)) {
                    console.error("Invalid response structure");
                    return;
                }

                $('.header-mini-cart').empty(); // Clear previous items
                let total = 0;
                let itemCount = 0; // Initialize item count

                if (response.items.length === 0) {
                    $('.header-mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        if (!item.product || !item.product.price || !item.product.name) {
                            console.error("Invalid item structure");
                            return;
                        }

                        const discount = item.product.discount || 0; // Get discount percentage
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount
                        const itemTotal = discountedPrice * item.quantity; // Calculate total for this item
                        itemCount += item.quantity; // Sum the quantities for the cart count

                        $('.header-mini-cart').append(`
                            <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
                                <div class="mini-cart-img" style="margin-right: 10px;">
                                    <img src="${item.product_image || 'path/to/default/image.jpg'}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="mini-cart-content" style="flex-grow: 1;">
                                    <h4 class="product-title" style="margin: 0; font-size: 14px;">
                                        <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>
                                    </h4>
                                    <div class="mini-cart-price" style="margin-top: 5px;">
                                        ${item.quantity} ×
                                        <span class="woocommerce-Price-amount amount" style="color: red;">$${discountedPrice.toFixed(2)}</span>

                                    </div>
                                </div>
                                <div class="remove-button" style="margin-left: auto;">
                                    <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
                                </div>
                            </div>
                        `);

                        total += itemTotal; // Sum the total amount
                    });

                    $('.header-mini-cart').append(`
                        <p class="woocommerce-mini-cart__total">
                            <strong>Subtotal:</strong>
                            <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>
                        </p>
                        <p class="checkout-link">
                            <a href="/viewCart" class="button wc-forward">View cart</a>
                            <a href="/Checkout" class="button checkout wc-forward">Checkout</a>
                        </p>
                    `);
                    $('.mini-cart-count').text(itemCount); // Update the cart count in the header
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function () {
        $('.header-shop-cart a').on('click', function () {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });
    // Remove item from mini cart
    $(document).on('click', '.remove', function (event) {
        event.preventDefault(); // Prevent the default link behavior

        const productId = $(this).data('product_id');

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.success) {
                    // alert(response.message);
                    fetchCart(); // Refresh the cart items
                } else {
                    alert('Failed to remove item from cart.'); // Handle unexpected response
                }
            },
            error: function (xhr, status, error) {
                // Check if the response contains a JSON message
                try {
                    const errResponse = JSON.parse(xhr.responseText);
                    alert(errResponse.message || 'Failed to remove item from cart.');
                } catch (e) {
                    alert('Failed to remove item from cart.');
                }
            }
        });
    });


</script>

<script>
    $(document).ready(function() {
        $('.category-button').on('click', function() {
            // Remove 'active' class from all buttons
            $('.category-button').removeClass('active');

            // Add 'active' class to the clicked button
            $(this).addClass('active');
        });
    });
</script>
</body>


</html>


