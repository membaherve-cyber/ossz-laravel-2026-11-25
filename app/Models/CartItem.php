<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{

    // Schema has created_at only.
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = ['cart_id', 'variant_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
