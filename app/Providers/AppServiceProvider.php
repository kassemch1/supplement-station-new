<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Services\Catalog;
use App\Services\PageCache;
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

        // Stored store pages go out of date when something they show changes
        PageCache::watchModels();
        Paginator::useBootstrap();

        // The header menu lists the categories (with their subcategories) and the brands on every store page.
        // Categories without products are left out of every menu
        View::composer('partials.navBar', function ($view) {
            $counts = Catalog::categoryCounts();
            $view->with([
                'categories' => Category::cachedAll()->filter(fn (Category $category) => ($counts[$category->id] ?? 0) > 0)->values(),
                'categoryTree' => Category::treeWithProducts($counts),
                'brands' => Brand::cachedAll(),
            ]);
        });
        View::composer('partials.footer', fn ($view) => $view->with('footerCategories', Category::treeWithProducts()));
    }
}
