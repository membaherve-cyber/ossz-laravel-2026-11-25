<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = ['url', 'alt_text', 'tags', 'uploaded_by'];
}
