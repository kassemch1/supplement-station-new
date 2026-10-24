<?php

namespace App\Http\Controllers\user_controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SpecialOffer;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        $categories = Category::cachedAll();

        // The menu, the home search, the shop search and the sidebar each send their own parameter
        if ($request->filled('category-nav')) {
            $query->where('category_id', $request->input('category-nav'));
        }

        $currentCategoryIdSideBar = null;
        if ($request->filled('category-sidebar')) {
            $currentCategoryIdSideBar = (int) $request->input('category-sidebar');
            $query->where('category_id', $currentCategoryIdSideBar);
        }

        foreach (['home-search', 'shop-search'] as $search) {
            if ($request->filled($search)) {
                $query->where('name', 'like', '%' . $request->input($search) . '%');
            }
        }

        // Category buttons (wider screens) and the category dropdown (phones) send the name
        foreach (['category', 'category-phone'] as $field) {
            if ($request->filled($field) && $category = $categories->firstWhere('name', $request->input($field))) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->input('orderby') === 'low-to-high') {
            $query->orderByRaw('(price * (1 - discount / 100)) ASC');
        } elseif ($request->input('orderby') === 'high-to-low') {
            $query->orderByRaw('(price * (1 - discount / 100)) DESC');
        } else {
            $query->orderBy('created_at', 'ASC');
        }

        $perPage = 12;
        $products = $query->with('images')->withCount('reviews')->withAvg('reviews', 'rating')->paginate($perPage);
        $totalProducts = $products->total();
        $startIndex = $totalProducts > 0 ? ($products->currentPage() - 1) * $perPage + 1 : 0;
        $endIndex = $totalProducts > 0 ? min($products->currentPage() * $perPage, $totalProducts) : 0;

        // Filtering, sorting and paging reload only the product list
        if ($request->ajax()) {
            return response()->json([
                'productList' => view('partials.product_list', ['product' => $products])->render(),
                'paginatee' => view('partials.product_pagination', ['product' => $products])->render(),
                'productsIndexing' => view('partials.products_indexing', compact('startIndex', 'endIndex', 'totalProducts'))->render(),
            ]);
        }

        $offerProductIds = SpecialOffer::orderBy('position')->orderBy('id')
            ->limit(4)
            ->pluck('product_id')
            ->toArray();

        if (!empty($offerProductIds)) {
            $offers = Product::whereIn('id', $offerProductIds)
                ->with('images')
                ->get()
                ->sortBy(fn ($p) => array_search($p->id, $offerProductIds))
                ->values();
        } else {
            $offers = Product::inRandomOrder()->with('images')->take(4)->get();
        }

        return view('user_views.Shop', [
            'product' => $products,
            'categories' => $categories,
            'offersProducts' => $offers,
            'totalProducts' => $totalProducts,
            'startIndex' => $startIndex,
            'endIndex' => $endIndex,
            'currentCategoryId' => $currentCategoryIdSideBar,
        ]);
    }
}
