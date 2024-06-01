<?php

namespace Uneca\Chimera\Traits;

use Uneca\Chimera\Services\AreaTree;

trait AreaResolver
{
    public function areaResolver(): array
    {
        $filtersToApply = [
            ...(($this->isBeingFeatured || $this->linkedFromScorecard) ? [] : session()->get('area-filter', [])),
            ...auth()->user()->areaRestrictionAsFilter(),
        ];
        $path = AreaTree::getFinestResolutionFilterPath($filtersToApply);
        $expandedPath = AreaTree::pathAsFilter($path);
        return [$path, $expandedPath];
    }
}
