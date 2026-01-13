<?php

namespace Uneca\Chimera\Traits;

use Uneca\Chimera\Livewire\AreaFilter;
use Uneca\Chimera\Services\AreaTree;

trait AreaResolver
{
    public function shouldIgnoreFilterInSession(string $placement): bool
    {
        //return ($this?->isBeingFeatured ?? false) || $this instanceof CaseStats || $this instanceof ScorecardComponent;
        return ($this?->isBeingFeatured ?? false) || $placement === 'dashboard';
    }

    public function areaResolver(): array
    {
        $sessionKey = $this->placement === 'area-insights' ? AreaFilter::AREA_INSIGHTS_SESSION_KEY : AreaFilter::SESSION_KEY;
        $filtersToApply = [
            ...($this->shouldIgnoreFilterInSession($this->placement) ? [] : session()->get($sessionKey, [])),
            ...auth()->user()->areaRestrictionAsFilter(),
        ];
        $path = AreaTree::getFinestResolutionFilterPath($filtersToApply);
        $expandedPath = AreaTree::pathAsFilter($path);
        return [$path, $expandedPath];
    }
}
