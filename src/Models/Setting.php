<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;


class Setting extends Model
{
    protected $guarded = ['id'];

    public function scopeDirectlyEditable($query)
    {
        return $query->where('directly_editable', true);
    }
}
