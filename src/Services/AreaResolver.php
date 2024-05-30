<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\User;

class AreaResolver
{
    public string $path = '';
    public array $expandedPath = [];

    public function __construct()
    {
        $filtersToApply = [
            ...session()->get('area-filter', []),
            ...auth()->user()->areaRestrictionAsFilter(),
        ];
        $this->path = AreaTree::getFinestResolutionFilterPath($filtersToApply);
        $this->expandedPath = AreaTree::pathAsFilter($this->path);
    }
}
