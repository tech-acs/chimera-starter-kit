<?php

namespace Uneca\Chimera\Traits;

use Uneca\Chimera\Services\AreaTree;

trait FilterBasedAxisTitle
{
    protected function getAreaBasedAxisTitle(string $filterPath, bool $includeParent = false): string
    {
        $areaTree = new AreaTree();
        //$path = AreaTree::getFinestResolutionFilterPath($filter);
        $depth = collect(explode('.', $filterPath))->filter()->count();
        $levelName = $areaTree->hierarchies[$depth];
        $title = str($levelName)->plural()->title();
        if ($depth > 0 && $includeParent) {
            $previousLevel = $areaTree->getArea($filterPath);
            $title .= " of " . $previousLevel->name . ' ' . $areaTree->hierarchies[$previousLevel->level];
        }
        return $title;
    }

}
