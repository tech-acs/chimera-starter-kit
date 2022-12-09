<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\AreaHierarchy;
use SplDoublyLinkedList;

class AreaTree
{
    public array $hierarchies;  // [ 0 => 'admin level one',  1 => 'admin level two', ... ]
    private SplDoublyLinkedList $hierarchiesDll;

    public function __construct(int $removeLastNLevels = 0)
    {
        //$hierarchies = config('chimera.area.hierarchies');
        $hierarchies = AreaHierarchy::orderBy('index')->pluck('name')->all();
        $this->hierarchies = array_slice($hierarchies, 0, count($hierarchies) - $removeLastNLevels);

        $this->hierarchiesDll = new SplDoublyLinkedList;
        foreach ($hierarchies as $hierarchy) {
            $this->hierarchiesDll->push($hierarchy);
        }
    }

    public function getHierarchy(string $order = 'top-down'): SplDoublyLinkedList
    {
        if ($order === 'bottom-up') {
            $this->hierarchiesDll->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
        } else {
            $this->hierarchiesDll->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
        }
        $this->hierarchiesDll->rewind();
        return $this->hierarchiesDll;
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

    public function levelFromPath(string $path)
    {
        return str($path)->explode('.')->count() - 1;
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
