<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Translatable\HasTranslations;

class AreaHierarchy extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['name'];
    protected $casts = ['map_zoom_levels' => 'array'];

    public function inapplicableIndicators(): MorphToMany
    {
        return $this->morphedByMany(Indicator::class, 'inapplicable');
    }

    public function inapplicableScorecards(): MorphToMany
    {
        return $this->morphedByMany(Scorecard::class, 'inapplicable');
    }

    public function inapplicableGauges(): MorphToMany
    {
        return $this->morphedByMany(Gauge::class, 'inapplicable');
    }
}
