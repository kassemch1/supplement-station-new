<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'subscribed_at', 'is_active'];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // The admin list and its CSV export: search by email, filter by active/inactive
    public function scopeFilter($query, ?string $search, ?string $status)
    {
        return $query
            ->when($search, fn ($q) => $q->where('email', 'like', '%' . $search . '%'))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($q) => $q->where('is_active', $status === 'active'));
    }

    // The link in every newsletter. It is signed, so only the person who got the email can use it,
    // and it never expires (an old email must still let them leave). The signature covers the path
    // only, so it holds whether the site is reached over http or https, with or without www.
    public function unsubscribeUrl(): string
    {
        return url(URL::signedRoute('newsletter.unsubscribe', ['subscription' => $this->id], absolute: false));
    }
}
