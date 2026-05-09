<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;

class RecentPurchaseController extends Controller
{
    public function index(): JsonResponse
    {
        $items = OrderItem::with(['product.images', 'order.shippingDetail'])
            ->whereHas('product', function ($q) {
                $q->where('stock', '>', 0);
            })
            ->whereHas('order', function ($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            })
            ->whereHas('order.shippingDetail')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $payload = $items->map(function ($item) {
            $product = $item->product;
            $shipping = $item->order->shippingDetail ?? null;
            $img = $product?->images->first();

            return [
                'product_id' => $product?->id,
                'product_name' => $product?->name,
                'image' => $img ? asset($img->url) : null,
                'city' => $shipping?->city,
                'time_ago' => $item->created_at->diffForHumans(),
                'product_url' => $product ? route('products.show', $product->id) : '#',
            ];
        })
        ->filter(fn($p) => $p['product_id'] && $p['product_name'])
        ->values();

        return response()->json($payload);
    }
}
