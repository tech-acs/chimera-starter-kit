<?php

namespace Uneca\Chimera\Traits;

use Uneca\Chimera\Livewire\CaseStats;
use Uneca\Chimera\Livewire\ScorecardComponent;
use Uneca\Chimera\Services\AreaTree;

trait AreaResolver
{
    public function shouldIgnoreFilterInSession(): bool
    {
        return ($this?->isBeingFeatured ?? false) || $this instanceof CaseStats || $this instanceof ScorecardComponent;
    }

    public function areaResolver(): array
    {
        $filtersToApply = [
            ...($this->shouldIgnoreFilterInSession() ? [] : session()->get('area-filter', [])),
            ...auth()->user()->areaRestrictionAsFilter(),
        ];
        $path = AreaTree::getFinestResolutionFilterPath($filtersToApply);
        $expandedPath = AreaTree::pathAsFilter($path);
        return [$path, $expandedPath];
    }
}
