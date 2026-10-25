<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    const TYPE_REUSABLE = 'reusable';
    const TYPE_SINGLE_USE = 'single_use';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'discount_percentage',
        'minimum_order_amount',
        'max_uses',
        'used_count',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // The admin types the expiry in store time and it has always been saved as typed, so the column
    // holds Beirut wall-clock time (not UTC like the other dates). Reading it in that zone makes
    // "expires at 23:59" end at 23:59 in Beirut, for the coupons already saved and the new ones.
    protected function expiresAt(): Attribute
    {
        $zone = config('app.display_timezone');

        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value, $zone) : null,
            set: fn ($value) => $value ? Carbon::parse($value, $zone)->setTimezone($zone)->format('Y-m-d H:i:s') : null,
        );
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired() && !$this->isUsedUp();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // Single-use coupons once used, reusable ones at their limit
    public function isUsedUp(): bool
    {
        if ($this->type === self::TYPE_SINGLE_USE) {
            return $this->used_count > 0;
        }

        return $this->max_uses && $this->used_count >= $this->max_uses;
    }

    public function canApplyToOrder($orderAmount): bool
    {
        return $orderAmount >= $this->minimum_order_amount;
    }

    public function calculateDiscount($orderTotal): float
    {
        return round($orderTotal * $this->discount_percentage / 100, 2);
    }

    // Counts one use; a single-use coupon is switched off by it
    public function apply(): void
    {
        $this->increment('used_count');

        if ($this->type === self::TYPE_SINGLE_USE) {
            $this->update(['is_active' => false]);
        }
    }

    // Why a coupon can't be used right now, in the customer's words (null when it can)
    public function problem(): ?string
    {
        if (!$this->is_active) {
            return 'This coupon has been deactivated.';
        }
        if ($this->isExpired()) {
            return 'This coupon expired on ' . $this->expires_at->format('M d, Y h:i A') . ' (Beirut time).';
        }
        if ($this->isUsedUp()) {
            return $this->type === self::TYPE_SINGLE_USE
                ? 'This coupon is no longer valid.'
                : 'This coupon has reached its maximum usage limit.';
        }

        return null;
    }

    public function getFormattedDiscountAttribute(): string
    {
        return $this->discount_percentage . '%';
    }

    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return $this->type === self::TYPE_SINGLE_USE && $this->used_count > 0 ? 'Used' : 'Inactive';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }
        if ($this->type === self::TYPE_REUSABLE && $this->isUsedUp()) {
            return 'Max Uses Reached';
        }

        return 'Active';
    }

    // Switched on and not expired (compared in the same store time the column is stored in)
    public function scopeActive($query)
    {
        $now = Carbon::now(config('app.display_timezone'))->format('Y-m-d H:i:s');

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now));
    }
}
