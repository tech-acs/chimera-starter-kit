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

    public static function getFinestResolutionFilterPath(array $filter): string
    {
        return array_reduce($filter, function ($carriedLongest, $path) {
            return strlen($path) >= strlen($carriedLongest) ? $path : $carriedLongest;
        }, '');
    }

    public static function translatePathToCode(array $filter): array
    {
        $filterAreas = Area::whereIn('path', array_values($filter))->pluck('code', 'path')->all();
        return array_map(function ($path) use ($filterAreas) {
            return $filterAreas[$path];
        }, $filter);
    }

    public static function translateCodeToPath(array $filter): array
    {
        $filterAreas = Area::whereIn('code', array_values($filter))->pluck('code', 'path')->all();
        return array_map(function ($path) use ($filterAreas) {
            return $filterAreas[$path];
        }, $filter);
    }

    public static function levelFromPath(string $path)
    {
        return str($path)->explode('.')->count() - 1;
    }

    public function areas(?string $parentPath = null, string $orderBy = 'name', bool $checksumSafe = true, ?string $nameOfReferenceValueToInclude = null)
    {
        $lquery = empty($parentPath) ? '*{1}' : "$parentPath.*{1}";
        if (is_null($nameOfReferenceValueToInclude)) {
            return Area::selectRaw($checksumSafe ? "CONCAT('*', path) AS path, code, name" : 'path, code, name')
                ->whereRaw("path ~ '{$lquery}'")
                ->orderBy($orderBy)
                ->get();
        } else {
            return Area::selectRaw($checksumSafe ? "CONCAT('*', areas.path) AS path, code, name, value" : 'path, code, name, value')
                ->leftJoin('reference_values', 'areas.path', 'reference_values.path')
                ->whereRaw("areas.path ~ '{$lquery}' AND reference_values.indicator = '{$nameOfReferenceValueToInclude}'")
                ->orderBy($orderBy)
                ->get();
        }
    }

    public function getArea(string $path)
    {
        return Area::select('path', 'code', 'name', 'level')
            ->whereRaw("path ~ '{$path}'")
            ->first();
    }
}
