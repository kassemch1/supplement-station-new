<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use App\Services\ImageFiles;

class ProductImagesController extends Controller
{
    public function destroy(int $id)
    {
        $image = ProductImage::findOrFail($id);
        $image->delete();
        // Removes the file too, unless an order line (or anything else) still shows it
        ImageFiles::forget('product_images', $image->id, $image->url);

        return back()->with('success', 'Image deleted successfully');
    }
}
