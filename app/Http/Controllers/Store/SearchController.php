<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\Catalog;
use App\Services\ProductSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // What the search boxes show while the visitor types
    public function suggest(Request $request): JsonResponse
    {
        $query = mb_substr(trim(Catalog::text($request, 'q') ?? ''), 0, 100);
        if (mb_strlen($query) < 2) {
            return response()->json(['products' => [], 'brands' => [], 'categories' => [], 'total' => 0]);
        }

        return response()->json(ProductSearch::suggest($query) + [
            'total' => count(ProductSearch::ids($query)),
            'all' => route('shop', ['q' => $query]),
        ]);
    }
}
