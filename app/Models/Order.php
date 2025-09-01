<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_amount',
        'subtotal_amount',
        'status',
        'coupon_id',
        'coupon_code',
        'coupon_discount_amount',
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

    /**
     * Check if order used a coupon
     */
    public function hasCoupon(): bool
    {
        return !is_null($this->coupon_id) && $this->coupon_discount_amount > 0;
    }
}