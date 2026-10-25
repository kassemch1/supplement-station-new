<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // The header menu lists the categories (with their subcategories) and the brands on every store page
        View::composer('partials.navBar', fn ($view) => $view->with([
            'categories' => Category::cachedAll(),
            'categoryTree' => Category::tree(),
            'brands' => Brand::cachedAll(),
        ]));
        View::composer('partials.footer', fn ($view) => $view->with('footerCategories', Category::tree()));
    }
}
