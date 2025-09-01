<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin | Create Coupon</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/helper.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
    <link id="cus-style" rel="stylesheet" href="{{ asset('admin_assets/css/style-primary.css') }}">
    <style>
    body.skin-dark {
        background-color: #202334;
        color: #F5F7FA;
    }

    /* Card Styling */
    .card {
        background: #2B2F42 !important;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: none;
    }

    .card-body {
        padding: 2.5rem;
    }

    /* Form Fields */
    .form-control,
    .input-group-text {
        background: #fff;
        border-radius: 6px;
        border: 1px solid #3A7DFF22;
        color: #202334;
    }

    /* Labels */
    label {
        font-weight: 600;
        color: #F5F7FA;
    }

    /* Buttons */
    .btn-primary {
        background-color: #3A7DFF;
        border-color: #3A7DFF;
    }

    .btn-primary:hover {
        background-color: #2E6AE6;
        border-color: #2E6AE6;
    }

    .btn-secondary {
        background-color: #B0BEC5;
        border-color: #B0BEC5;
        color: #202334;
    }

    .btn-secondary:hover {
        background-color: #A0B0B8;
        border-color: #A0B0B8;
        color: #202334;
    }

    /* Headings */
    h3 {
        color: #F5F7FA;
    }

    /* Helper text */
    .form-text {
        color: #B0BEC5;
    }
</style>

</head>

<body class="skin-dark">

<div class="main-wrapper">

    <!-- Header Section Start -->
    @include('partials/adminHeader')
    <!-- Header Section End -->

    <!-- Side Header Start -->
    @include('partials/adminSideBar')
    <!-- Side Header End -->

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Create New Coupon</h3>
                </div>
            </div><!-- Page Heading End -->

            <!-- Page Button Start -->
            <div class="col-12 col-lg-auto mb-20">
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                    <i class="zmdi zmdi-arrow-back"></i> Back to Coupons
                </a>
            </div><!-- Page Button End -->

        </div><!-- Page Headings End -->

        <!-- Create Coupon Form Start -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.coupons.store') }}">
                            @csrf

                            <div class="row">
                                <!-- Coupon Code -->
                                <div class="col-lg-6 col-12 mb-30">
                                    <label for="code">Coupon Code *</label>
                                    <input name="code" class="form-control @error('code') is-invalid @enderror"
                                           type="text" placeholder="e.g., SAVE20 or MY-CUSTOM-CODE"
                                           value="{{ old('code') }}" required>
                                    <small class="form-text">Codes are stored uppercase; duplicates are not allowed.</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Coupon Name -->
                                <div class="col-lg-6 col-12 mb-30">
                                    <label for="name">Coupon Name *</label>
                                    <input name="name" class="form-control @error('name') is-invalid @enderror" 
                                           type="text" placeholder="e.g., Summer Sale 20%" 
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Coupon Type -->
                                <div class="col-lg-6 col-12 mb-30">
                                    <label for="type">Coupon Type *</label>
                                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="reusable" {{ old('type') == 'reusable' ? 'selected' : '' }}>
                                            Reusable (Multiple users can use)
                                        </option>
                                        <option value="single_use" {{ old('type') == 'single_use' ? 'selected' : '' }}>
                                            Single Use (One-time use only)
                                        </option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Discount Percentage -->
                                <div class="col-lg-6 col-12 mb-30">
                                    <label for="discount_percentage">Discount Percentage *</label>
                                    <div class="input-group">
                                        <input name="discount_percentage" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                               type="number" step="0.01" min="0.01" max="100" 
                                               placeholder="e.g., 20" value="{{ old('discount_percentage') }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    @error('discount_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Minimum Order Amount -->
                                <div class="col-lg-6 col-12 mb-30">
                                    <label for="minimum_order_amount">Minimum Order Amount *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input name="minimum_order_amount" class="form-control @error('minimum_order_amount') is-invalid @enderror" 
                                               type="number" step="0.01" min="0" 
                                               placeholder="e.g., 50.00" value="{{ old('minimum_order_amount', 0) }}" required>
                                    </div>
                                    @error('minimum_order_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Max Uses (for reusable coupons) -->
                                <div class="col-lg-6 col-12 mb-30" id="max-uses-field" style="display: none;">
                                    <label for="max_uses">Maximum Uses</label>
                                    <input name="max_uses" class="form-control @error('max_uses') is-invalid @enderror" 
                                           type="number" min="1" placeholder="Leave empty for unlimited" 
                                           value="{{ old('max_uses') }}">
                                    <small class="form-text text-muted">Leave empty for unlimited uses</small>
                                    @error('max_uses')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Expiry Date -->
                                <div class="col-lg-6 col-12 mb-30">
                                    <label for="expires_at">Expiry Date</label>
                                    <input name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" 
                                           type="datetime-local" value="{{ old('expires_at') }}">
                                    <small class="form-text text-muted">Leave empty for no expiry</small>
                                    @error('expires_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-12 mb-30">
                                    <label for="description">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Optional description for this coupon">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="zmdi zmdi-save"></i> Create Coupon
                                    </button>
                                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                                        <i class="zmdi zmdi-close"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- Create Coupon Form End -->

    </div><!-- Content Body End -->

</div><!-- Main Wrapper End -->

<!-- JS -->
<script src="{{ asset('admin_assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/popper.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/plugins.js') }}"></script>
<script src="{{ asset('admin_assets/js/main.js') }}"></script>

<script>
// Show/hide max uses field based on coupon type
$(document).ready(function() {
    $('select[name="type"]').change(function() {
        if ($(this).val() === 'reusable') {
            $('#max-uses-field').show();
        } else {
            $('#max-uses-field').hide();
            $('input[name="max_uses"]').val('');
        }
    });

    // Trigger change event on page load
    $('select[name="type"]').trigger('change');
});
</script>

</body>
</html>