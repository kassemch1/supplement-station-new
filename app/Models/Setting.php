<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Values the admin changes in Admin > Settings, one row per key. A key without a row uses the default
// its reader gives.
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function read(string $key, ?string $default = null): ?string
    {
        return static::whereKey($key)->value('value') ?? $default;
    }

    public static function write(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
