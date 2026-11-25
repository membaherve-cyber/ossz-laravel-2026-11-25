<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /** The migration creates "audit_log" (singular) — keep them in sync. */
    protected $table = 'audit_log';

    // Schema has created_at only.
    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'actor_email', 'action', 'detail'];
}
