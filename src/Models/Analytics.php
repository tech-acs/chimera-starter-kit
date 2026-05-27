<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['started_at' => 'immutable_datetime'];

    public $timestamps = false;

    public function analyzable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
