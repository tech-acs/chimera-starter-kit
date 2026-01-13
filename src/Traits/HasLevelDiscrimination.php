<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Services\AreaTree;

trait HasLevelDiscrimination
{
    public function inapplicableLevels(): MorphToMany
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
        $levels = collect(app('hierarchies'))->prepend('National');
        return $this->inapplicableLevels->pluck('name')->doesntContain($levels[$level]);
    }
}
