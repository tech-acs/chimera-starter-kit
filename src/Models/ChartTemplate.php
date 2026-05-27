<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;

class ChartTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'layout' => 'array',
    ];
}
