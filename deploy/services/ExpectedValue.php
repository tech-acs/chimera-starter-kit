<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExpectedValue
{
    protected ?string $connection;
    protected Collection $levels;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection;
        $this->levels = (new AreaTree($this->connection))->levels();
    }

    private function nextLevelDown($currentLevel)
    {
        $levels = $this->levels->flip();
        $currentKeyIndex = $levels->search($currentLevel);
        return $levels[$currentKeyIndex + 1] ?? null;
    }

    public function values(string $indicator, array $filter = [])
    {
        $givenAreaType = $this->levels->intersectByKeys($filter)->sort()->keys()->last();
        $desiredAreaType = $this->nextLevelDown($givenAreaType);
        $topAreaLevel = $this->levels->keys()->first();

        return DB::table('expected_values')
            ->join('areas', function ($join) use ($filter, $desiredAreaType) {
                $join->on('expected_values.area_code', '=', 'areas.code')
                    ->where('areas.connection_name', $this->connection);
            })
            ->select('expected_values.area_code AS code', 'expected_values.value', 'areas.type AS area_type')
            ->where('expected_values.indicator', 'ILIKE', $indicator)
            ->when(
                $desiredAreaType,
                function ($query, $desiredAreaType) use ($filter, $givenAreaType) {
                    return $query
                        ->where('areas.type', $desiredAreaType)
                        ->where('areas.parent_code', $filter[$givenAreaType] ?? null);
                },
                function ($query) use ($topAreaLevel) {
                    return $query->where('areas.type', $topAreaLevel);
                }
            )
            ->get();
    }
}
