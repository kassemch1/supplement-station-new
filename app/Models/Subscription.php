<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'subscribed_at',
        'is_active'
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Scope to get active subscriptions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if email is already subscribed
     */
    public static function isSubscribed($email)
    {
        return static::where('email', $email)->where('is_active', true)->exists();
    }
}
