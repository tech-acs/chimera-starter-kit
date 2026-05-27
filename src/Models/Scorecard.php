<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Enums\ScorecardScope;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;
use Uneca\Chimera\Traits\HasLevelDiscrimination;

class Scorecard extends Model
{
    use HasDashboardEntityCommonalities;
    use HasFactory;
    use HasLevelDiscrimination;
    use HasTranslations;

    protected $guarded = ['id'];

    public $translatable = ['title'];

    public $permissionSuffix = ':scorecard';

    protected $casts = [
        'scope' => ScorecardScope::class,
    ];

    protected $with = ['inapplicableLevels'];

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('started_at');
    }

    public function scopeScope($query, $type = ScorecardScope::Dashboard)
    {
        return $query->whereIn('scope', [$type, ScorecardScope::Everywhere]);
    }
}
