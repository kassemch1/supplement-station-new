<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

// /sitemap.xml, built from the database on each request: new, renamed and archived products show up
// (or drop out) at once, with nothing to regenerate
class SitemapController extends Controller
{
    public function index()
    {
        $products = Product::with(['images' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'category_id', 'updated_at']);

        // A category page changes when one of its products does
        $categoryUpdated = DB::table('products')->whereNull('deleted_at')
            ->groupBy('category_id')
            ->pluck(DB::raw('MAX(updated_at)'), 'category_id');

        $categories = Category::cachedAll()->filter(fn ($category) => $categoryUpdated->has($category->id));

        return response()
            ->view('sitemap', [
                'products' => $products,
                'categories' => $categories,
                'categoryUpdated' => $categoryUpdated,
                'lastUpdate' => $products->max('updated_at'),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
