<?php

namespace Tests;

use App\Models\Product;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // A product page address (/products/{slug}); /products/{id} only redirects there
    protected function productUrl($id): string
    {
        return Product::withTrashed()->findOrFail($id)->url();
    }
}
