<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;

class AreaRestriction extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
