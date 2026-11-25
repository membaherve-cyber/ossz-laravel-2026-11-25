<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id', 'appointment_id', 'channel', 'recipient', 'subject',
        'body', 'status', 'error',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
