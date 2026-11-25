<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{

    // Schema has created_at only.
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [
        'category', 'question', 'answer', 'category_fr', 'question_fr', 'answer_fr', 'sort_order',
    ];

    protected $casts = ['sort_order' => 'integer'];
}
