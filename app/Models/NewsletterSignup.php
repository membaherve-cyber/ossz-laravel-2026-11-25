<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSignup extends Model
{

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = ['email'];
}
