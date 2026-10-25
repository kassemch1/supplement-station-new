<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'expires_at'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Sum of the lines at today's prices. Load items.product first to keep it to one query.
    public function subtotal(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->lineTotal());
    }
}
