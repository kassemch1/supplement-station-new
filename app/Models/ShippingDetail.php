<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingDetail extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'name', 'phone', 'email', 'address', 'city'];

    protected static function booted(): void
    {
        static::saving(fn (self $detail) => $detail->phone_key = static::phoneKey($detail->phone));
    }

    // The phone as plain digits in one form, so the same number typed differently is found and grouped
    // as one customer: no spaces or signs, no 961 / 00961 country code, and the 0 of a 7-digit local
    // number put back ("+961 3 123 456" and "03 123 456" are both 03123456)
    public static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        $digits = preg_replace('/^(00)?961(?=\d{7,8}$)/', '', $digits);
        if (strlen($digits) === 7) {
            $digits = '0' . $digits;
        }

        return $digits === '' ? null : substr($digits, 0, 20);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
