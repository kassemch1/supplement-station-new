<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Catalog;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    // Parameters the menus and filters sent before categories had their own pages
    private const OLD_CATEGORY_PARAMS = ['category-nav', 'category-sidebar', 'category', 'category-phone'];

    private const PER_PAGE = 24;

    public function index(Request $request)
    {
        $hasOldParams = (bool) array_intersect(self::OLD_CATEGORY_PARAMS, array_keys($request->query()));

        // /shop, /Shop, and ?category=..., become the front page or /shop/{slug} with the other parameters kept
        if (!$request->ajax() && ($request->path() !== '/' || $hasOldParams)) {
            $category = $this->categoryFromOldParams($request);
            $query = array_filter(
                $request->except([...self::OLD_CATEGORY_PARAMS, '_token', 'post_type']),
                fn ($value) => $value !== null && $value !== '' && $value !== 'sort'
            );

            return redirect(($category ? $category->url() : url('/') . '/') . ($query ? '?' . http_build_query($query) : ''), 301);
        }

        // A page opened before the move may still filter by these parameters
        return $this->listing($request, $hasOldParams ? $this->categoryFromOldParams($request) : null);
    }

    // /shop/{slug}: one category's products (a main category includes its subcategories)
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

        // A category with no products is not shown anywhere; an old link to it opens the whole shop
        // (temporarily: the page comes back once products are added to it)
        if ((Catalog::categoryCounts()[$category->id] ?? 0) === 0) {
            return redirect()->route('shop');
        }

        return $this->listing($request, $category);
    }

    private function listing(Request $request, ?Category $category)
    {
        $filters = Catalog::filters($request);
        $scope = Catalog::scope($category, $filters['search']);
        $products = Catalog::sort(Catalog::apply(clone $scope, $filters), $filters['sort'], $filters['search'])
            ->withCardData()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $ajax = $request->ajax() || $request->wantsJson();

        // A page number past the end is not a page
        abort_if(!$ajax && $products->currentPage() > max(1, $products->lastPage()), 404);

        $data = $this->viewData($category, $filters, $scope, $products);

        // Filters, sorting, "load more" and moving between categories happen without reloading the page:
        // the page asks for the parts that change
        if ($ajax) {
            $cards = view('partials.shop.cards', $data)->render();

            return response()->json([
                'title' => $data['pageTitle'] . ' | Supplement Station',
                'nav' => view('partials.shop.nav', $data)->render(),
                'sidebar' => view('partials.shop.sidebar', $data)->render(),
                'results' => view('partials.shop.results', $data)->render(),
                'cards' => $cards,
                'next' => $products->nextPageUrl(),
                'total' => $products->total(),
                'to' => $products->lastItem() ?? 0,
                // What shop pages cached before the redesign read
                'productList' => $cards,
                'paginatee' => '',
                'productsIndexing' => '',
            ]);
        }

        return view('user_views.Shop', $data);
    }

    private function viewData(?Category $category, array $filters, $scope, $products): array
    {
        // Categories without products are left out of the menu
        $counts = Catalog::categoryCounts();
        $tree = Category::treeWithProducts($counts);

        $brandCounts = Catalog::brandCounts($scope);
        $brands = Brand::cachedAll()
            ->filter(fn ($brand) => isset($brandCounts[$brand->id]))
            ->sortByDesc(fn ($brand) => $brandCounts[$brand->id])
            ->values();

        $search = $filters['search'];
        $filtered = $filters['brands']->isNotEmpty() || $filters['min'] !== null || $filters['max'] !== null
            || $filters['in_stock'] || $filters['sale'] || $filters['sort'] !== 'default';
        $page = $products->currentPage();
        $baseUrl = $category ? $category->url() : url('/') . '/';

        return [
            'products' => $products,
            'category' => $category,
            'main' => $category?->main(),
            'tree' => $tree,
            'counts' => $counts,
            'brands' => $brands,
            'brandCounts' => $brandCounts,
            'filters' => $filters,
            'filtered' => $filtered,
            'priceRange' => Catalog::priceRange($scope),
            'sorts' => Catalog::SORTS,
            'search' => $search,
            'heading' => $search !== null ? "Results for \"{$search}\"" : ($category ? $category->name : 'All Supplements'),
            'baseUrl' => $baseUrl,
            'frontPage' => !$category && $search === null && $page === 1,
            'totalProducts' => $products->total(),
            'startIndex' => $products->firstItem() ?? 0,
            'pageTitle' => ($category ? $category->name . ' in Lebanon' : 'Shop All Supplements in Lebanon') . ($page > 1 ? ' - Page ' . $page : ''),
            // Search engines index each category page and its numbered pages; filtered and sorted lists
            // point to the plain list, search results are not indexed
            'canonical' => $page > 1 ? $baseUrl . '?page=' . $page : $baseUrl,
            'noindex' => $search !== null || $filtered || $products->total() === 0,
            'prevUrl' => !$filtered && $page > 1 ? ($page === 2 ? $baseUrl : $baseUrl . '?page=' . ($page - 1)) : null,
            'nextUrl' => !$filtered && $products->hasMorePages() ? $baseUrl . '?page=' . ($page + 1) : null,
        ];
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
