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

    public static function translatePathToCode(array $paths): array
    {
        $areas = Area::whereIn('path', array_values($paths))->pluck('code', 'path')->all();
        return array_map(function ($path) use ($areas) {
            return $areas[$path];
        }, $paths);
    }

    public static function pathAsFilter(string $path): array
    {
        $ancestorsInclusive = [$path];
        $keepMovingUp = true;
        $path = str($path);
        while ($keepMovingUp) {
            if ($path->contains('.')) {
                $remainingPath = $path->beforeLast('.');
                array_push($ancestorsInclusive, $remainingPath->toString());
                $path = $remainingPath;
            } else {
                $keepMovingUp = false;
            }
        }
        $areas = Area::whereIn('path', $ancestorsInclusive)->get(['code', 'level']);
        $hierarchies = (new AreaTree())->hierarchies;
        return $areas->mapWithKeys(function ($area) use ($hierarchies) {
            return [$hierarchies[$area->level] => $area->code];
        })->all();
    }

    public static function levelFromPath(string $path)
    {
        return str($path)->explode('.')->count() - 1;
    }

    public function areas(?string $parentPath = null, string $orderBy = 'name', bool $checksumSafe = false, ?string $nameOfReferenceValueToInclude = null)
    {
        $lquery = empty($parentPath) ? '*{1}' : "$parentPath.*{1}";
        if (is_null($nameOfReferenceValueToInclude)) {
            return Area::selectRaw($checksumSafe ? "CONCAT('*', areas.path) AS path, code, name" : 'areas.path, code, name')
                ->whereRaw("path ~ '{$lquery}'")
                ->orderBy($orderBy)
                ->get();
        } else {
            return Area::selectRaw($checksumSafe ? "CONCAT('*', areas.path) AS path, code, name, value" : 'areas.path, code, name, value')
                ->leftJoin('reference_values', 'areas.path', 'reference_values.path')
                ->whereRaw("areas.path ~ '{$lquery}' AND COALESCE(reference_values.indicator, '{$nameOfReferenceValueToInclude}') = '{$nameOfReferenceValueToInclude}'")
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
