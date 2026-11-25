<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{

    // Schema has created_at only.
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = ['product_id', 'url', 'alt_text', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
