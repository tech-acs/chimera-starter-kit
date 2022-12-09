<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Area extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['name'];

    public function scopeOfLevel(Builder $query, $level)
    {
        return $query->where('level', $level);
    }
}
