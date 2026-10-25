<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'option_id', 'option_value', 'image', 'stock'];

    // Optional picture of this value (a flavour tub, a colour), shown on the option button
    public function imageUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
