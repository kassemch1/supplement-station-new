<?php

/*
| Store and admin CSS/JS bundles, built by `php artisan assets:build` into public/assets/build (or the
| bundle's own 'output'). Paths are relative to the project root. Files ending in .min.css/.min.js are
| copied as they are, everything else is minified. After changing any file listed here, run the command
| and commit the built files (the live server has no build tools).
*/

return [

    'output' => 'public/assets/build',

    'bundles' => [
        'store.css' => [
            'public/assets/css/bootstrap.min.css',
            'public/assets/css/fontawesome.css',
            'public/assets/css/animate.css',
            'public/assets/css/swiper.min.css',
            'public/assets/css/main.css',
            'resources/assets/css/store.css',
            'resources/assets/css/ss.css',
            'resources/assets/css/ss-pages.css',
        ],
        'store.js' => [
            'public/assets/js/jquery-3.7.1.min.js',
            'public/assets/js/bootstrap.bundle.min.js',
            'public/assets/js/swiper.min.js',
            'public/assets/js/wow.min.js',
            'public/assets/js/touchspin.js',
            'public/assets/js/jquery.easing.js',
            'public/assets/js/main.js',
            'resources/assets/js/store.js',
        ],

        'home.css' => ['resources/assets/css/pages/home.css'],
        'shop.css' => ['resources/assets/css/pages/shop.css'],
        'product.css' => ['resources/assets/css/pages/product.css'],
        'cart.css' => ['resources/assets/css/pages/cart.css'],
        'checkout.css' => ['resources/assets/css/pages/checkout.css'],

        'shop.js' => ['resources/assets/js/pages/shop.js'],
        'product.js' => ['resources/assets/js/pages/product.js'],
        'cart.js' => ['resources/assets/js/pages/cart.js'],
        'checkout.js' => ['resources/assets/js/pages/checkout.js'],

        // Admin (layouts/admin). Built into public/admin_assets/build so the template's ../fonts and
        // ../images paths keep working. admin.css is the template's CSS with the rules no admin page uses
        // taken out (see 'purge'); admin-theme.css is the admin look and comes after the pages' own styles.
        'admin.css' => [
            'output' => 'public/admin_assets/build',
            'files' => [
                'public/admin_assets/css/vendor/bootstrap.min.css',
                'public/admin_assets/css/vendor/material-design-iconic-font.min.css',
                'public/admin_assets/css/vendor/font-awesome.min.css',
                'public/admin_assets/css/plugins/plugins.css',
                'public/admin_assets/css/helper.css',
                'public/admin_assets/css/style.css',
            ],
            'purge' => 'admin',
        ],
        'admin-theme.css' => [
            'output' => 'public/admin_assets/build',
            'files' => ['resources/assets/admin/admin-theme.css'],
        ],
        'admin.js' => [
            'output' => 'public/admin_assets/build',
            'files' => [
                'public/admin_assets/js/vendor/jquery-3.3.1.min.js',
                'public/admin_assets/js/vendor/popper.min.js',
                'public/admin_assets/js/vendor/bootstrap.min.js',
                'public/admin_assets/js/plugins/select2/select2.full.min.js',
                'resources/assets/admin/admin-base.js',
                'resources/assets/admin/admin-ui.js',
            ],
        ],
    ],

    // Unused-rule removal (App\Support\CssPurge): a class or id is in use when it appears as a word in
    // one of these files, or matches a safelist pattern (classes the views build from pieces, like
    // badge-{{ ... }}). After adding a class to an admin page, rebuild: php artisan assets:build
    'purge' => [
        'admin' => [
            'content' => [
                'resources/views/admin_views',
                'resources/views/partials/adminHeader.blade.php',
                'resources/views/partials/adminSideBar.blade.php',
                'resources/views/partials/adminFooter.blade.php',
                'resources/views/layouts/admin.blade.php',
                'app/Http/Controllers/Admin',
                'vendor/laravel/framework/src/Illuminate/Pagination/resources/views/bootstrap-4.blade.php',
                'vendor/laravel/framework/src/Illuminate/Pagination/resources/views/simple-bootstrap-4.blade.php',
                'public/admin_assets/js/vendor/bootstrap.min.js',
                'public/admin_assets/js/plugins/select2/select2.full.min.js',
                'resources/assets/admin',
            ],
            'safelist' => ['/^(badge|button|btn|bg|text|alert|border)-/'],
        ],
    ],

];
