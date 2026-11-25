<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{

    // Schema has created_at only.
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [
        'name', 'method', 'fee', 'eta_label', 'name_fr', 'eta_label_fr', 'is_active',
    ];

    protected $casts = ['fee' => 'integer', 'is_active' => 'boolean'];
}
