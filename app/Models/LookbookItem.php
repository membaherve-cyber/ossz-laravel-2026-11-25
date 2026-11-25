<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookbookItem extends Model
{

    // Schema has created_at only.
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [
        'title', 'caption', 'caption_fr', 'image_url', 'media_type',
        'video_url', 'poster_url', 'duration_seconds', 'product_slug', 'sort_order',
    ];

    protected $casts = ['duration_seconds' => 'integer', 'sort_order' => 'integer'];
}
