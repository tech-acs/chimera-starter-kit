<?php

namespace Uneca\Chimera\Traits;

use Uneca\Chimera\Services\AreaTree;

trait FilterBasedAxisTitle
{
    protected function getFinestResolutionFilterPath(array $filter): string
    {
        return array_reduce($filter, function ($carriedLongest, $path) {
            return strlen($path) >= strlen($carriedLongest) ? $path : $carriedLongest;
        }, '');
    }

    protected function getAreaBasedAxisTitle($filter): string
    {
        $areaTree = new AreaTree();
        $path = $this->getFinestResolutionFilterPath($filter);
        $depth = collect(explode('.', $path))->filter()->count();
        $levelName = $areaTree->hierarchies[$depth];
        $title = str($levelName)->plural()->title();
        if ($depth > 0) {
            $previousLevel = $areaTree->getArea($path);
            $title .= " of " . $previousLevel->name . ' ' . $areaTree->hierarchies[$previousLevel->level];
        }
        return $title;
    }

}
