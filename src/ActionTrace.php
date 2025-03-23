<?php

namespace EduLazaro\Laractions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActionTrace extends Model
{
    protected $fillable = [
        'actor_type', 'actor_id',
        'target_type', 'target_id',
        'action',
        'params'
    ];

    protected $casts = [
        'params' => 'array',
    ];

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
