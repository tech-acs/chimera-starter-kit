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

    public function toFilter()
    {
        return array_filter([
            'region' => $this->region_code ?? null,
            'regionName' => $this->region_name ?? null,
            'district' => $this->district_code ?? null,
            'districtName' => $this->district_name ?? null,
            'sa' => $this->sa_code ?? null,
            'saName' => $this->sa_name ?? null,
        ]);
    }
}
