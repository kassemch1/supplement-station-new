<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\admin_controllers\Controller;
use App\Models\Product;
use App\Models\SpecialOffer;
use Illuminate\Http\Request;

class SpecialOfferController extends Controller
{
    const MAX_OFFERS = 6;

    public function index()
    {
        $offers = SpecialOffer::with('product.images')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $usedIds = $offers->pluck('product_id')->toArray();

        $availableProducts = Product::whereNotIn('id', $usedIds)
            ->orderBy('name')
            ->get();

        return view('admin_views.manage_special_offers.index', [
            'offers' => $offers,
            'availableProducts' => $availableProducts,
            'maxOffers' => self::MAX_OFFERS,
            'isFull' => $offers->count() >= self::MAX_OFFERS,
        ]);
    }

    public function store(Request $request)
    {
        if (SpecialOffer::count() >= self::MAX_OFFERS) {
            return redirect()->route('admin.specialOffers.index')
                ->withErrors(['product_id' => 'You can only have ' . self::MAX_OFFERS . ' special offers. Remove one before adding another.']);
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id|unique:special_offers,product_id',
        ], [
            'product_id.unique' => 'This product is already a special offer.',
        ]);

        SpecialOffer::create([
            'product_id' => $validated['product_id'],
            'position' => $this->firstFreePosition(),
        ]);

        return redirect()->route('admin.specialOffers.index')
            ->with('success', 'Product added to special offers.');
    }

    public function update(Request $request, $id)
    {
        $offer = SpecialOffer::findOrFail($id);

        $validated = $request->validate([
            'position' => 'required|integer|between:1,' . self::MAX_OFFERS,
        ], [
            'position.between' => 'Position must be between 1 and ' . self::MAX_OFFERS . '.',
        ]);

        $newPosition = (int) $validated['position'];

        // If another offer already holds this position, swap with it.
        $conflict = SpecialOffer::where('position', $newPosition)
            ->where('id', '!=', $offer->id)
            ->first();

        if ($conflict) {
            $conflict->update(['position' => $offer->position]);
        }

        $offer->update(['position' => $newPosition]);

        return redirect()->route('admin.specialOffers.index')
            ->with('success', 'Position updated.');
    }

    public function destroy($id)
    {
        $offer = SpecialOffer::findOrFail($id);
        $offer->delete();

        return redirect()->route('admin.specialOffers.index')
            ->with('success', 'Removed from special offers.');
    }

    private function firstFreePosition(): int
    {
        $taken = SpecialOffer::pluck('position')->all();
        for ($i = 1; $i <= self::MAX_OFFERS; $i++) {
            if (!in_array($i, $taken)) {
                return $i;
            }
        }
        return self::MAX_OFFERS;
    }
}
