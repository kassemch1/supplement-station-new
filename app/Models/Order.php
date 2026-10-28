<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Cancelled orders are kept (not deleted) but don't count as sales
    public const STATUSES = ['pending', 'delivered', 'cancelled'];

    // How the customer gets the order: brought to their address, or collected at the store (no address)
    public const DELIVERY = 'delivery';
    public const PICKUP = 'pickup';
    public const DELIVERY_METHODS = [self::DELIVERY, self::PICKUP];

    public function scopeCountsAsSale($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    // The admin's order search: "#597" or "597" is an order number, digits are also looked for in the
    // phone (in any of the ways it was typed, see ShippingDetail::phoneKey), and words in the name
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }
        $digits = preg_replace('/\D+/', '', $term);

        return $query->where(function ($where) use ($term, $digits) {
            $where->whereRaw('1 = 0');   // a search with nothing to look for finds nothing
            if (preg_match('/^#?\s*\d{1,7}$/', $term)) {
                $where->orWhere('orders.id', (int) $digits);
            }
            if (strlen($digits) >= 3) {
                $key = strlen($digits) >= 7 ? ShippingDetail::phoneKey($digits) : $digits;
                $where->orWhereHas('shippingDetail', fn ($s) => $s->where('phone_key', 'like', '%' . $key . '%'));
            }
            if (preg_match('/\pL/u', $term)) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $where->orWhereHas('shippingDetail', fn ($s) => $s->where('name', 'like', $like));
            }
        });
    }

    protected $fillable = [
        'total_amount',
        'subtotal_amount',
        'status',
        'delivery_method',
        'coupon_id',
        'coupon_code',
        'coupon_discount_amount',
        'checkout_token',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingDetail()
    {
        return $this->hasOne(ShippingDetail::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted subtotal amount
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal_amount, 2);
    }

    /**
     * Get formatted coupon discount
     */
    public function getFormattedDiscountAttribute(): string
    {
        return '$' . number_format($this->coupon_discount_amount, 2);
    }

    public function isPickup(): bool
    {
        return $this->delivery_method === self::PICKUP;
    }

    /**
     * Check if order used a coupon
     */
    public function hasCoupon(): bool
    {
        return !is_null($this->coupon_id) && $this->coupon_discount_amount > 0;
    }
}