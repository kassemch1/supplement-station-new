<?php

namespace App\Http\Controllers\admin_controllers;


use App\Models\ProductImage;
use App\Services\ImageFiles;
use Illuminate\Http\Request;

class ProductImagesController
{
    public function destroy($id)
    {
        // Find and delete the image
        $image = ProductImage::findOrFail($id);
        $image->delete();
        // Removes the file too, unless an order line (or anything else) still shows it
        ImageFiles::forget('product_images', $image->id, $image->url);

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Image deleted successfully');
    }


}
