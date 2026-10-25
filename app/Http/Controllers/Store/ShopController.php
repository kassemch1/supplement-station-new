<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SpecialOffer;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    // Parameters the menus and filters sent before categories had their own pages
    private const OLD_CATEGORY_PARAMS = ['category-nav', 'category-sidebar', 'category', 'category-phone'];

    public function index(Request $request)
    {
        $hasOldParams = (bool) array_intersect(self::OLD_CATEGORY_PARAMS, array_keys($request->query()));

        // /Shop, and /shop?category=..., become /shop or /shop/{slug} with the other parameters kept
        if (!$request->ajax() && ($request->path() !== 'shop' || $hasOldParams)) {
            $category = $this->categoryFromOldParams($request);
            $query = array_filter(
                $request->except([...self::OLD_CATEGORY_PARAMS, '_token', 'post_type']),
                fn ($value) => $value !== null && $value !== '' && $value !== 'sort'
            );

            return redirect(($category ? $category->url() : route('shop')) . ($query ? '?' . http_build_query($query) : ''), 301);
        }

        // A page opened before the move may still filter by these parameters
        return $this->listing($request, $hasOldParams ? $this->categoryFromOldParams($request) : null);
    }

    // /shop/{slug}: one category's products
    public function category(Request $request, string $key)
    {
        $categories = Category::cachedAll();
        $category = $categories->first(fn ($c) => $c->slug !== null && strcasecmp($c->slug, $key) === 0);

        if (!$category) {
            // Older links: /shop/3, /shop/{old slug} (category renamed), /shop/Creatine (category name)
            $id = ctype_digit($key) ? (int) $key : Category::idForOldSlug(strtolower($key));
            $found = $id ? $categories->firstWhere('id', $id)
                : $categories->first(fn ($c) => strcasecmp($c->name, urldecode($key)) === 0);
            abort_unless($found, 404);

            return $this->permanentRedirect($found->url(), $request);
        }

        if ($category->slug !== $key) {
            return $this->permanentRedirect($category->url(), $request);
        }

        return $this->listing($request, $category);
    }

    private function listing(Request $request, ?Category $category)
    {
        $query = Product::query();
        $categories = Category::cachedAll();

        if ($category) {
            $query->where('category_id', $category->id);
        }

        // The menu search, the home search and the shop search each send their own parameter
        $search = null;
        foreach (['shop-search', 'home-search'] as $field) {
            if ($request->filled($field)) {
                $search = (string) $request->input($field);
                $query->where('name', 'like', '%' . $search . '%');
            }
        }

        if ($request->input('orderby') === 'low-to-high') {
            $query->orderByRaw('(price * (1 - discount / 100.0)) ASC');
        } elseif ($request->input('orderby') === 'high-to-low') {
            $query->orderByRaw('(price * (1 - discount / 100.0)) DESC');
        } else {
            $query->orderBy('created_at', 'ASC');
        }

        $perPage = 12;
        $products = $query->withCardData()->paginate($perPage);
        $totalProducts = $products->total();
        $startIndex = $totalProducts > 0 ? ($products->currentPage() - 1) * $perPage + 1 : 0;
        $endIndex = $totalProducts > 0 ? min($products->currentPage() * $perPage, $totalProducts) : 0;

        // Searching, sorting and paging reload only the product list
        if ($request->ajax()) {
            return response()->json([
                'productList' => view('partials.product_list', ['product' => $products])->render(),
                'paginatee' => view('partials.product_pagination', ['product' => $products])->render(),
                'productsIndexing' => view('partials.products_indexing', compact('startIndex', 'endIndex', 'totalProducts'))->render(),
            ]);
        }

        // A page number past the end is not a page
        abort_if($products->currentPage() > max(1, $products->lastPage()), 404);

        $offerIds = SpecialOffer::productIds(4);
        $offers = $offerIds ? Product::cards($offerIds) : Product::inRandomOrder()->with('images')->take(4)->get();

        // Search engines index each category page and its numbered pages; sorted lists point to the
        // plain list, search results are not indexed
        $baseUrl = $category ? $category->url() : route('shop');
        $page = $products->currentPage();

        return view('user_views.Shop', [
            'product' => $products,
            'categories' => $categories,
            'category' => $category,
            'offersProducts' => $offers,
            'totalProducts' => $totalProducts,
            'startIndex' => $startIndex,
            'endIndex' => $endIndex,
            'search' => $search,
            'canonical' => $page > 1 ? $baseUrl . '?page=' . $page : $baseUrl,
            'noindex' => $search !== null || $totalProducts === 0,
            'prevUrl' => $page > 1 ? ($page === 2 ? $baseUrl : $baseUrl . '?page=' . ($page - 1)) : null,
            'nextUrl' => $products->hasMorePages() ? $baseUrl . '?page=' . ($page + 1) : null,
        ]);
    }

    // The category the menu (id) or the filters (name) asked for with the old parameters
    private function categoryFromOldParams(Request $request): ?Category
    {
        $categories = Category::cachedAll();
        $category = null;
        foreach (['category-nav', 'category-sidebar'] as $field) {
            if ($request->filled($field)) {
                $category = $categories->firstWhere('id', (int) $request->query($field)) ?? $category;
            }
        }
        foreach (['category', 'category-phone'] as $field) {
            if ($request->filled($field)) {
                $category = $categories->first(fn ($c) => strcasecmp($c->name, (string) $request->query($field)) === 0) ?? $category;
            }
        }

        return $category;
    }

    private function permanentRedirect(string $url, Request $request)
    {
        $query = $request->getQueryString();

        return redirect($url . ($query ? '?' . $query : ''), 301);
    }
}
