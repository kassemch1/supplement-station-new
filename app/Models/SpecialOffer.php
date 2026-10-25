<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialOffer extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'position'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Product ids in the admin's order, for the home page and the shop sidebar
    public static function productIds(int $limit): array
    {
        return static::orderBy('position')->orderBy('id')->limit($limit)->pluck('product_id')->all();
    }
}
