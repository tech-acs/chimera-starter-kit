<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;
use Uneca\Chimera\Traits\HasLevelDiscrimination;

class Indicator extends Model
{
    use HasTranslations;
    use HasDashboardEntityCommonalities;
    use HasLevelDiscrimination;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description', 'help'];
    public $permissionSuffix = ':indicator';
    protected $casts = [
        'data' => 'array',
        'layout' => 'array'
    ];
    protected $with = ['inapplicableLevels'];

    public function pages(): MorphToMany
    {
        return $this->morphToMany(Page::class, 'pageable')
            ->withPivot('rank')
            ->withTimestamps();
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')
            ->orderBy('started_at');
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

    protected function component(): Attribute
    {
        return new Attribute(
            get: fn () => $this->slug,
        );
    }

    public function scopeOfTag(Builder $query, $tag)
    {
        return $query->where('tag', $tag);
    }

    public function scopeUntagged(Builder $query)
    {
        return $query->where('tag', null);
    }
}
