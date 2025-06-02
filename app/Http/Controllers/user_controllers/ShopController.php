<?php

namespace App\Http\Controllers\user_controllers;


use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class ShopController extends Controller
{

//    public function Shop()
//    {
//
//
//        $agent = new Agent();
//        $categories = Category::all();
//        $search = request()->query('search');
//        $orderBy = request()->query('orderby');
//        $categoryName = request()->query('category');
//        $page = request()->query('page', 1); // Default to page 1
//
//        $productQuery = Product::query()->with('images');
//
//        if ($categoryName) {
//            $category = Category::where('name', $categoryName)->first();
//            if (!$category) {
//                return response()->json(['error' => 'Category not found.'], 404);
//            }
//            $productQuery->where('category_id', $category->id);
//        }
//
//        if ($search) {
//            $productQuery->where('name', 'like', '%' . $search . '%');
//        }
//
//        // Calculate the discounted price and apply sorting
//        $productQuery->selectRaw('*, (price - (price * discount / 100)) as discounted_price');
//
//        if ($orderBy === 'price') {
//            $productQuery->orderBy('discounted_price', 'asc'); // Ascending order
//        } elseif ($orderBy === 'price-desc') {
//            $productQuery->orderBy('discounted_price', 'desc'); // Descending order
//        }
//
//        // Apply pagination
//        $products = $productQuery->paginate(6, ['*'], 'page', $page);
//
//        // Get offers products
//        $offersCategory = Category::where('name', 'Offers')->first();
//        $offersProducts = [];
//
//        if ($offersCategory) {
//            $offersProducts = Product::where('category_id', $offersCategory->id)
//                                     ->take(4)
//                                     ->with('images')
//                                     ->get();
//
//
//            if ($offersProducts->isEmpty()) {
//                $offersProducts = Product::inRandomOrder()
//                                         ->take(4)
//                                         ->with('images')
//                                         ->get();
//            }
//        } else {
//
//            $offersProducts = Product::inRandomOrder()
//                                     ->take(4)
//                                     ->with('images')
//                                     ->get();
//        }
//
//
//        if (request()->ajax()) {
//            return response()->json([
//                'product' => $products->items(),
//                'pagination' => [
//                    'current_page' => $products->currentPage(),
//                    'last_page' => $products->lastPage(),
//                ],
//
//            ]);
//        }
//
//        return view('user_views.Shop', [
//            'product' => $products,
//            'categories' => $categories,
//            'selectedCategory' => $categoryName,
//            'offersProducts'=>$offersProducts,
//            'agent'=>$agent,
//        ]);
//    }

    public function index(Request $request)
    {
        // Initialize the query
        $query = Product::query();

        if($request->has('category-nav') && $request->input('category-nav') !== ''){
            $category_id=$request->input('category-nav');
            $query->where('category_id', $category_id);

        }

        $currentCategoryIdSideBar=null;
        if($request->has('category-sidebar') && $request->input('category-sidebar') !== ''){
            $category_id=(int)$request->input('category-sidebar');
            $currentCategoryIdSideBar=$category_id;
            $query->where('category_id', $category_id);

        }
        if($request->has('home-search') && $request->input('home-search') !== ''){
            $searchTerm=$request->input('home-search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            });
        }
        if($request->has('shop-search') && $request->input('shop-search') !== ''){
            $searchTerm=$request->input('shop-search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            });
        }
        // Apply category filtering if a category is selected
        if ($request->has('category') && $request->input('category') !== '') {
            $categoryName = $request->input('category');
            $category = Category::where('name', $categoryName)->first();

            if ($category) {
                $query->where('category_id', $category->id);
            }
        }


        if ($request->has('category-phone') && $request->input('category-phone') !== '') {
            $categoryName = $request->input('category-phone');
            $category = Category::where('name', $categoryName)->first();

            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Apply sorting based on the 'orderby' parameter if present
        if ($request->input('orderby') === 'low-to-high') {
            $query->orderByRaw('(price * (1 - discount / 100)) ASC');
        } elseif ($request->input('orderby') === 'high-to-low') {
            $query->orderByRaw('(price * (1 - discount / 100)) DESC');
        }
        else {
            // Default sorting (e.g., by creation date or id)
            $query->orderBy('created_at', 'ASC'); // or any other default attribute
        }

        // Paginate the results
        $totalProducts = $query->count();
        $perPage = 12;
        $products = $query->with('images')->paginate($perPage);




        // Calculate the start and end indices based on the paginated results
        if($products->total() > 0) {
            $startIndex = ($products->currentPage() - 1) * $perPage + 1;
            $endIndex = min($products->currentPage() * $perPage, $products->total());
        } else {
            $startIndex = 0;
            $endIndex = 0;
        }





        $categories = Category::all();
        $offersCategory = Category::where('name', 'Offers')->first();
        $offers = Product::where('category_id', $offersCategory->id)->take(4)->get();
        $agent=new Agent();
        if ($request->ajax()) {
            $productList = view('partials.product_list', ['product' => $products,'agent'=>$agent,])->render();
            $pagination = view('partials.product_pagination', ['product' => $products])->render();
            $productsIndexing=view('partials.products_indexing',['startIndex'=>$startIndex,'endIndex'=>$endIndex,'totalProducts'=>$totalProducts])->render();
            return response()->json([
                'productList' => $productList,
                'paginatee' => $pagination,
                'productsIndexing'=>$productsIndexing
            ]);
        }



        // Return the view with data
        return view('user_views.Shop', [
            'product' => $products,
            'categories' => $categories,
            'offersProducts' => $offers,
            'agent'=>$agent,
            'totalProducts'=>$totalProducts,
            'startIndex'=>$startIndex,
            'endIndex'=>$endIndex,
            'currentCategoryId'=>$currentCategoryIdSideBar
        ]);
    }



//    public function homeSearchToShop(Request $request)
//    {
//        $searchTerm=$request->input('home-search');
//
//        $products = DB::table('products')->where('name','LIKE','%'.$searchTerm.'%')
//            ->get();
//
//        return view('user_views/shop',[
//            'products'=>$products
//
//        ]);
//    }
}
