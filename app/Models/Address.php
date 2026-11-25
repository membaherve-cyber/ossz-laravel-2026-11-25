<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'label', 'full_name', 'phone', 'city', 'area', 'street', 'notes', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
