<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{

    // Schema has created_at only.
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id', 'size', 'colour', 'sku', 'price_override',
        'stock_qty', 'low_stock_threshold',
    ];

    protected $casts = [
        'price_override' => 'integer',
        'stock_qty' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
