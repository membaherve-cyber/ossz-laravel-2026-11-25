<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'session_id', 'coupon_code'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
