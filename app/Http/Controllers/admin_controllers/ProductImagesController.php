<?php

namespace App\Http\Controllers\admin_controllers;


use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImagesController
{
    public function destroy($id)
    {
        // Find and delete the image
        $image = ProductImage::findOrFail($id);
        $image->delete();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Image deleted successfully');
    }


}
