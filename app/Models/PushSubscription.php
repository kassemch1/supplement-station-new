<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A phone or computer that gets a notification for every new order (App\Services\AdminPush)
class PushSubscription extends Model
{
    protected $fillable = ['user_id', 'endpoint_hash', 'endpoint', 'public_key', 'auth_token', 'device'];

    public static function hashOf(string $endpoint): string
    {
        return hash('sha256', $endpoint);
    }

    // "iPhone", "Mac"... from the browser's user agent, so the admin can tell the devices apart
    public static function deviceName(?string $userAgent): string
    {
        foreach (['iPhone' => 'iPhone', 'iPad' => 'iPad', 'Android' => 'Android phone', 'Macintosh' => 'Mac', 'Windows' => 'Windows PC'] as $needle => $name) {
            if (str_contains((string) $userAgent, $needle)) {
                return $name;
            }
        }

        return 'Other device';
    }
}
