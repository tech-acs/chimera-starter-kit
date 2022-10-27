<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AreaTree
{
    public array $hierarchies;  // [ 0 => 'region',  1 => 'constituency', ... ]

    public function __construct()
    {
        $this->hierarchies = config('chimera.area.hierarchies');
        array_pop($this->hierarchies); // Remove last element (ea)
    }

    public function areas(?string $parentPath = null, string $orderBy = 'name', bool $checksumSafe = true)
    {
        $lquery = empty($parentPath) ? '*{1}' : "$parentPath.*{1}";
        return DB::table('areas')
            ->selectRaw($checksumSafe ? "CONCAT('*', path) AS path, code, name" : 'path, code, name')
            ->whereRaw("path ~ '{$lquery}'")
            ->orderBy($orderBy)
            ->get();
    }

    public function getArea(string $path)
    {
        return DB::table('areas')
            ->select('path', 'code', 'name', 'level')
            ->whereRaw("path ~ '{$path}'")
            ->first();
    }

    public function prev($levelName)
    {
        $key = array_search($levelName, $this->hierarchies);
        return $key === false ? null : $this->hierarchies[$key - 1] ?? null;
    }

    public function next($levelName)
    {
        $key = array_search($levelName, $this->hierarchies);
        return $key === false ? null : $this->hierarchies[$key + 1] ?? null;
    }

    public function nextLevelNames($levelName)
    {
        $currentKey = array_search($levelName, $this->hierarchies);
        return array_slice(array_values($this->hierarchies), $currentKey + 1);
    }
}
