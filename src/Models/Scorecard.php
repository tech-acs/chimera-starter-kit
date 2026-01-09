<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Enums\ScorecardScope;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;
use Uneca\Chimera\Traits\HasLevelDiscrimination;

class Scorecard extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasDashboardEntityCommonalities;
    use HasLevelDiscrimination;

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

    /*public function inapplicableLevels(): MorphToMany
    {
        return $this->morphToMany(AreaHierarchy::class, 'inapplicable')
            ->withTimestamps();
    }

    public function supportsLevel(string $filterPath): bool
    {
        if ($this->inapplicableLevels->isEmpty()) {
            return true;
        }
        $level = empty($filterPath) ? 0 : AreaTree::levelFromPath($filterPath) + 1;
        return $this->inapplicableLevels->pluck('name')->doesntContain(app('hierarchies')[$level]);
    }*/

    public function scopeScope($query, $type = ScorecardScope::Dashboard)
    {
        return $query->whereIn('scope', [$type, ScorecardScope::Everywhere]);
    }
}
