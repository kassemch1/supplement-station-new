<?php

/*
| Store page CSS/JS bundles, built by `php artisan assets:build` into public/assets/build.
| Paths are relative to the project root. Files ending in .min.css/.min.js are copied as they are,
| everything else is minified. After changing any file listed here, run the command and commit
| public/assets/build (the live server has no build tools).
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
    ],

];
