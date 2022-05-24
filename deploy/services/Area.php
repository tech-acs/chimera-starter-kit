<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Area
{
    protected ?string $connection;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection;
    }

    public function resolveSmallestFilter(array $filter)
    {
        return DB::table('areas')
            ->select('level', 'type', 'code', 'name')
            ->where('connection_name', $this->connection)
            ->whereIn('code', array_values($filter))
            ->orderBy('level', 'DESC')
            ->limit(1)
            ->first();
    }

    public function getAreaNamesForCurrentFilter(array $filter)
    {
        $area = null;
        if (! empty($filter)) {
            $area = $this->resolveSmallestFilter($filter);
        }
        return $this->areasByParent($area?->code);
    }

    public function levels()
    {
        return DB::table('areas')
            ->distinct('level')
            ->select('type', 'level')
            ->where('connection_name', $this->connection)
            ->orderBy('level')
            ->pluck('level', 'type');
    }

    // To get the actual codes from this method, use named arguments (>= PHP 8.0)
    // You can invoke it like so: areas(checksumSafe: false)
    public function areas(string $parent = null, string $orderBy = 'name', bool $checksumSafe = true, string $type = null)
    {
        return DB::table('areas')
            ->selectRaw($checksumSafe ? "CONCAT('*', code) AS code, name" : 'code, name')
            ->where('connection_name', $this->connection)
            ->where('parent_code', $parent)
            ->when($type, fn ($query, $type) => $query->where('type', $type))
            ->orderBy($orderBy)
            ->get();
    }

    public function areasByLevel(int $level, string $orderBy = 'name')
    {
        return DB::table('areas')
            ->select(['code', 'name'])
            ->where('connection_name', $this->connection)
            ->where('level', $level)
            ->orderBy($orderBy)
            ->get();
    }

    public function areasByType(?string $type, string $orderBy = 'name')
    {
        return DB::table('areas')
            ->select(['code', 'name'])
            ->where('connection_name', $this->connection)
            ->where('type', $type)
            ->orderBy($orderBy)
            ->get();
    }

    public function areasByParent(?string $parentCode, string $orderBy = 'name')
    {
        return DB::table('areas')
            ->select(['code', 'name'])
            ->where('connection_name', $this->connection)
            ->where('parent_code', $parentCode)
            ->orderBy($orderBy)
            ->get();
    }

    public function nextLevel(int $level): ?string
    {
        return $this->levels()->flip()[$level + 1] ?? null;
    }
}
