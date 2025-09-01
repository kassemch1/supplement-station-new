<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin | Edit Subscriber</title>
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

        .btn-danger {
            background-color: #F44336;
            border-color: #F44336;
        }

        .btn-danger:hover {
            background-color: #da190b;
            border-color: #da190b;
        }

        /* Headings */
        h3 {
            color: #F5F7FA;
        }

        /* Helper text */
        .form-text {
            color: #B0BEC5;
        }

        /* Checkbox custom styling */
        .form-check-input {
            width: 20px;
            height: 20px;
            background-color: #fff;
            border-color: #3A7DFF22;
        }

        .form-check-input:checked {
            background-color: #3A7DFF;
            border-color: #3A7DFF;
        }

        .form-check-label {
            color: #F5F7FA;
        }

        /* Subscriber info card */
        .subscriber-info {
            background-color: #333749;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #3A7DFF22;
        }

        .info-label {
            color: #B0BEC5;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: #F5F7FA;
            font-weight: 600;
            font-size: 1rem;
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
                    <h3>Edit Subscriber</h3>
                </div>
            </div><!-- Page Heading End -->

            <!-- Page Button Start -->
            <div class="col-12 col-lg-auto mb-20">
                <a href="{{ route('admin.subscribers.index') }}" class="btn btn-secondary">
                    <i class="zmdi zmdi-arrow-back"></i> Back to Subscribers
                </a>
            </div><!-- Page Button End -->

        </div><!-- Page Headings End -->

        <!-- Edit Subscriber Form Start -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Subscriber Info -->
                        <div class="subscriber-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-label">Subscribed Date:</div>
                                    <div class="info-value">{{ $subscriber->subscribed_at->format('M d, Y H:i') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Last Updated:</div>
                                    <div class="info-value">{{ $subscriber->updated_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.subscribers.update', $subscriber->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Email Address -->
                                <div class="col-lg-8 col-12 mb-30">
                                    <label for="email">Email Address *</label>
                                    <input name="email" class="form-control @error('email') is-invalid @enderror"
                                           type="email" placeholder="e.g., user@example.com"
                                           value="{{ old('email', $subscriber->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="col-lg-4 col-12 mb-30">
                                    <label>Subscription Status</label>
                                    <div class="form-check mt-2">
                                        <!-- Hidden input to ensure false value is sent when checkbox is unchecked -->
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                            {{ old('is_active', $subscriber->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Subscription
                                        </label>
                                    </div>
                                    <small class="form-text">Uncheck to deactivate this subscription</small>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="zmdi zmdi-save"></i> Update Subscriber
                                    </button>
                                    <a href="{{ route('admin.subscribers.index') }}" class="btn btn-secondary">
                                        <i class="zmdi zmdi-close"></i> Cancel
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deleteSubscriber()" style="float: right;">
                                        <i class="zmdi zmdi-delete"></i> Delete Subscriber
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Hidden delete form -->
                        <form id="deleteForm" action="{{ route('admin.subscribers.destroy', $subscriber->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- Edit Subscriber Form End -->

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
    function deleteSubscriber() {
        if (confirm('Are you sure you want to delete this subscriber? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</body>
</html>
