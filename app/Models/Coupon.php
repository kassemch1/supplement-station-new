<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

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
        'expires_at'
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // Constants for coupon types
    const TYPE_REUSABLE = 'reusable';
    const TYPE_SINGLE_USE = 'single_use';

    /**
     * Check if the coupon is valid for use
     */
    public function isValid(): bool
    {
        // Check if coupon is active
        if (!$this->is_active) {
            return false;
        }

        // Check if coupon has expired (using Beirut timezone)
        if ($this->expires_at && $this->expires_at->setTimezone('Asia/Beirut')->isPast()) {
            return false;
        }

        // Check if reusable coupon has reached max uses
        if ($this->type === self::TYPE_REUSABLE && $this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        // Single use coupons that have been used are invalid
        if ($this->type === self::TYPE_SINGLE_USE && $this->used_count > 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon has expired (Beirut timezone specific)
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        // Get current time in Beirut timezone
        $beirutNow = Carbon::now('Asia/Beirut');
        
        // Convert expiry time to Beirut timezone
        $expiryInBeirut = $this->expires_at->setTimezone('Asia/Beirut');
        
        return $beirutNow->gt($expiryInBeirut);
    }

    /**
     * Check if coupon can be applied to order amount
     */
    public function canApplyToOrder($orderAmount): bool
    {
        return $orderAmount >= $this->minimum_order_amount;
    }

    /**
     * Calculate discount amount for given order total
     */
    public function calculateDiscount($orderTotal): float
    {
        $discount = ($orderTotal * $this->discount_percentage) / 100;
        return round($discount, 2);
    }

    /**
     * Apply the coupon (increment usage count)
     */
    public function apply(): void
    {
        $this->increment('used_count');
        
        // If it's a single-use coupon, deactivate it after first use
        if ($this->type === self::TYPE_SINGLE_USE) {
            $this->update(['is_active' => false]);
        }
    }

    /**
     * Get formatted discount percentage
     */
    public function getFormattedDiscountAttribute(): string
    {
        return $this->discount_percentage . '%';
    }

    /**
     * Get status text with more detailed information (Beirut timezone aware)
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return $this->type === self::TYPE_SINGLE_USE && $this->used_count > 0 ? 'Used' : 'Inactive';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->type === self::TYPE_REUSABLE && $this->max_uses && $this->used_count >= $this->max_uses) {
            return 'Max Uses Reached';
        }

        return 'Active';
    }

    /**
     * Get remaining uses for reusable coupons
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->type === self::TYPE_SINGLE_USE) {
            return $this->used_count > 0 ? 0 : 1;
        }

        if ($this->max_uses) {
            return max(0, $this->max_uses - $this->used_count);
        }

        return null; // Unlimited
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): ?float
    {
        if (!$this->max_uses) {
            return null;
        }

        return ($this->used_count / $this->max_uses) * 100;
    }

    /**
     * Scope for active coupons (Beirut timezone aware)
     */
    public function scopeActive($query)
    {
        $beirutNow = Carbon::now('Asia/Beirut');
        
        return $query->where('is_active', true)
                    ->where(function ($q) use ($beirutNow) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', $beirutNow);
                    });
    }

    /**
     * Scope for valid coupons (active + not exceeded usage limits)
     */
    public function scopeValid($query)
    {
        return $query->active()
                    ->where(function ($q) {
                        $q->where('type', self::TYPE_REUSABLE)
                          ->where(function ($subQ) {
                              $subQ->whereNull('max_uses')
                                   ->orWhereRaw('used_count < max_uses');
                          })
                          ->orWhere(function ($subQ) {
                              $subQ->where('type', self::TYPE_SINGLE_USE)
                                   ->where('used_count', 0);
                          });
                    });
    }

    /**
     * Scope for reusable coupons
     */
    public function scopeReusable($query)
    {
        return $query->where('type', self::TYPE_REUSABLE);
    }

    /**
     * Scope for single-use coupons
     */
    public function scopeSingleUse($query)
    {
        return $query->where('type', self::TYPE_SINGLE_USE);
    }

    /**
     * Relationship with orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get expiry date in Beirut timezone for display
     */
    public function getExpiryInBeirutAttribute(): ?Carbon
    {
        if (!$this->expires_at) {
            return null;
        }

        return $this->expires_at->setTimezone('Asia/Beirut');
    }
}