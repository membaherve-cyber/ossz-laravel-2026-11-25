<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = ['name', 'contact', 'message', 'handled'];

    protected $casts = ['handled' => 'boolean'];
}
