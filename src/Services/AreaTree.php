<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\AreaHierarchy;

class AreaTree
{
    public array $hierarchies;  // [ 0 => 'admin level one',  1 => 'admin level two', ... ]

    public function __construct(int $removeLastNLevels = 0)
    {
        $hierarchies = AreaHierarchy::orderBy('index')->pluck('name')->all();
        $this->hierarchies = array_slice($hierarchies, 0, count($hierarchies) - $removeLastNLevels);
    }

    public function areas(?string $parentPath = null, string $orderBy = 'name', bool $checksumSafe = true)
    {
        $lquery = empty($parentPath) ? '*{1}' : "$parentPath.*{1}";
        return Area::selectRaw($checksumSafe ? "CONCAT('*', path) AS path, code, name" : 'path, code, name')
            ->whereRaw("path ~ '{$lquery}'")
            ->orderBy($orderBy)
            ->get();
    }

    public function getArea(string $path)
    {
        return Area::select('path', 'code', 'name', 'level')
            ->whereRaw("path ~ '{$path}'")
            ->first();
    }

    public static function levelFromPath(string $path)
    {
        return str($path)->explode('.')->count() - 1;
    }
}
