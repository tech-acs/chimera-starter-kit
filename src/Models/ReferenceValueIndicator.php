<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceValueIndicator extends Model
{
    protected $guarded = ['id'];

    public function getRouteKeyName()
    {
        return 'indicator';
    }

    public function referenceValues()
    {
        return $this->hasMany(ReferenceValue::class, 'indicator', 'indicator');
    }
}
