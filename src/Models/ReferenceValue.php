<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceValue extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function scopeOfLevel(Builder $query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeOfIndicator(Builder $query, $indicator)
    {
        return $query->where('indicator', $indicator);
    }
}
