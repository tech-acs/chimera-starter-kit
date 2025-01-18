<?php

namespace Uneca\Chimera\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function DeepCopy\deep_copy;

class BreakoutQueryBuilder
{
    private ConnectionInterface $dbConnection;
    private string $partialCaseIdentifyingCondition;
    private bool $excludePartials;
    private bool $excludeDeleted;
    private string $filterPath;
    private string $select;
    protected array $columns;
    private string $from;
    protected array $tables;
    private string $where;
    protected array $conditions;
    private string $groupBy;
    private string $having;
    private string $orderBy;
    private string $leftJoin = '';
    private string $joinColumn = 'area_code';
    private ?string $referenceValueToInclude = null;

    public function __construct(
        ?string $dataSource = null,
        string $filterPath = '',
        bool $excludePartials = true,
        bool $excludeDeleted = true,
        string $partialCaseIdentifyingCondition = 'cases.partial_save_mode is NULL'
    )
    {
        $this->filterPath = $filterPath;
        list($selectColumns, $whereConditions, $concernedTables) = QueryFragmentFactory::make($dataSource)->getSqlFragments($filterPath);

        try {
            $this->dbConnection = DB::connection($dataSource);
        } catch (\Exception $exception) {
            throw new \Exception("Data source named {$dataSource} is not connectable.");
        }

        $this->excludePartials = $excludePartials;
        $this->partialCaseIdentifyingCondition = $partialCaseIdentifyingCondition;
        $this->excludeDeleted = $excludeDeleted;
        $this->select = '';
        $this->columns = $selectColumns;
        $this->from = '';
        $this->tables = $concernedTables;
        $this->where = '';
        $this->conditions = ["cases.key != ''", ...$whereConditions];
        if ($this->excludeDeleted) {
            array_push($this->conditions, 'cases.deleted = 0');
        }
        if ($this->excludePartials) {
            array_push($this->conditions, $this->partialCaseIdentifyingCondition);
        }
        $this->groupBy = '';
        $this->having = '';
        $this->orderBy = '';
    }

    public function select(array $items) : self
    {
        $this->select = "SELECT " . implode(', ', array_merge($this->columns, $items));
        return $this;
    }

    public function from(array $items) : self
    {
        $fromClause = "(`level-1` INNER JOIN cases ON `level-1`.`case-id` = cases.id)";
        foreach (array_merge($this->tables, $items) as $item) {
            $fromClause .= " INNER JOIN $item ON `level-1`.`level-1-id` = $item.`level-1-id`";
        }
        $this->from = "FROM $fromClause";
        return $this;
    }

    public function where(array $items) : self
    {
        $this->conditions = array_merge($this->conditions, $items);
        return $this;
    }

    public function groupBy(array $items) : self
    {
        $this->groupBy = "GROUP BY " . implode(', ', $items);
        return $this;
    }

    public function having(array $items) : self
    {
        $this->having = "HAVING " . implode(' AND ', $items);
        return $this;
    }

    public function orderBy(array $items) : self
    {
        $this->orderBy = "ORDER BY " . implode(', ', $items);
        return $this;
    }

    public function dump(): self
    {
        dump($this->toSql());
        return $this;
    }

    public function debugLog(): self
    {
        logger($this->toSql());
        return $this;
    }

    public function toSql() : string
    {
        if (empty($this->from) && (count($this->tables) > 0)) {
            $this->from([]);
        }
        if (count($this->conditions) > 0) {
            $this->where = "WHERE " . implode(' AND ', $this->conditions);
        }
        return "{$this->select} {$this->from} {$this->where} {$this->groupBy} {$this->having} {$this->orderBy}";
    }

    private function makeTemplateRow($row)
    {
        $columns = array_keys((array) $row);
        $template = [];
        foreach ($columns as $column) {
            $template[$column] = null;
        }
        return (object) $template;
    }

    private function areaLeftJoinData(Collection $result, ?string $referenceValueToInclude): Collection
    {
        if ($result->isEmpty()) {
            return $result;
        }
        $data = deep_copy($result);

        $areas = (new AreaTree())->areas($this->filterPath, referenceValueToInclude: $referenceValueToInclude);
        $areasKeyByAreaCode = $areas->keyBy('code');
        $areaEnhancedData = $data->map(function ($row) use ($areasKeyByAreaCode, $referenceValueToInclude) {
            $area = $areasKeyByAreaCode->get($row->{$this->joinColumn});
            $area ??= (object) ['name' => 'Unknown'];
            $row->area_name = $area->name ?? null;
            $row->area_path = $area->path ?? null;
            if ($referenceValueToInclude) {
                $row->ref_value = $area->ref_value ?? null;
            }
            return $row;
        });

        $areaCodesPresentInData = $areaEnhancedData->pluck('area_code')->all();
        $areasMissingFromData = $areas->filter(fn ($area) => ! in_array($area->code, $areaCodesPresentInData));
        $newRowTemplate = $this->makeTemplateRow($areaEnhancedData->first());
        foreach ($areasMissingFromData as $area) {
            $newRowSkeleton = clone $newRowTemplate;
            $newRowSkeleton->area_name = $area->name;
            $newRowSkeleton->area_code = $area->code;
            $newRowTemplate->area_path = $area->path;
            if ($referenceValueToInclude) {
                $newRowSkeleton->ref_value = $area->ref_value ?? null;
            }
            $areaEnhancedData[] = $newRowSkeleton;
        }
        return $areaEnhancedData->sortBy('area_name');
    }

    private function areaRightJoinData(Collection $result, ?string $referenceValueToInclude): Collection
    {
        if ($result->isEmpty()) {
            return $result;
        }
        $data = deep_copy($result);

        $areas = (new AreaTree())->areas($this->filterPath, referenceValueToInclude: $referenceValueToInclude);
        $areasKeyByAreaCode = $areas->keyBy('code');
        return $data->map(function ($row) use ($areasKeyByAreaCode, $referenceValueToInclude) {
            $area = $areasKeyByAreaCode->get($row->{$this->joinColumn});
            $area ??= (object) ['name' => 'Unknown'];
            $row->area_name = $area->name ?? null;
            $row->area_path = $area->path ?? null;
            if ($referenceValueToInclude) {
                $row->ref_value = $area->ref_value ?? null;
            }
            return $row;
        });
    }

    private function areaCrossJoinData(Collection $result, ?string $referenceValueToInclude, array $lookup = [], string $columnName = null): Collection
    {
        // Return early if the input data is empty
        if ($result->isEmpty()) {
            return $result;
        }
        $data = deep_copy($result);
        $areas = (new AreaTree())->areas($this->filterPath, referenceValueToInclude: $referenceValueToInclude);

        // Cross-join lookup with areas to create a Cartesian product
        $lookupCollection = collect($lookup)->map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value
            ];
        });
        $crossJoinedData = $lookupCollection->crossJoin($areas);
        // Transform cross-joined data into enriched rows
        $newRowTemplate = $this->makeTemplateRow($data->first());
        $crossJoinedRows = $crossJoinedData->map(function ($pair) use ($columnName, $referenceValueToInclude,$newRowTemplate) {
            [$lookupItem, $area] = $pair;
            $newRow = clone $newRowTemplate;
            $newRow->{$columnName} = $lookupItem['key'] ?? null;
            $newRow->{$columnName . '_name'} = $lookupItem['value'] ?? null;
            $newRow->area_name = $area->name;
            $newRow->area_code = $area->code;
            $newRow->area_path = $area->path;
            if ($referenceValueToInclude) {
                $newRow->ref_value = $area->ref_value;
            }

            return $newRow;
        });
        // Enrich existing data with area and lookup details
        $areasKeyByAreaCode = $areas->keyBy('code');
        $areaEnhancedData = $data->map(function ($row) use ($areasKeyByAreaCode, $referenceValueToInclude,$columnName,$lookup) {
            $area = $areasKeyByAreaCode->get($row->{$this->joinColumn});
            $area ??= (object) ['name' => 'Unknown'];
            $row->area_name = $area->name ?? null;
            $row->area_path = $area->path ?? null;
            if ($referenceValueToInclude) {
                $row->ref_value = $area->ref_value;
            }
            $row->{$columnName . '_name'} = $lookup[$row->{$columnName}] ?? null;
            return $row;
        });
        // Filter cross-joined rows to exclude existing records
        $existingKeys = $areaEnhancedData->map(fn ($row) => [$row->{$columnName}, $row->area_code])->toArray();
        $filteredCrossJoinedRows = $crossJoinedRows->reject(function ($row) use ($existingKeys, $columnName) {
            return in_array([$row->{$columnName}, $row->area_code], $existingKeys);
        });

        // Combine existing data with the filtered cross-joined rows
        $combinedData = $areaEnhancedData->merge($filteredCrossJoinedRows);
        // Sort the combined data by area name and return it
        return $combinedData->sortBy([
            ['area_name', 'asc'],
            [$columnName ,'asc']
        ]);
    }

    public function lastlyAreaLeftJoinData(string $joinColumnOnDataSide = 'area_code', ?string $referenceValueToInclude = null): self
    {
        $this->leftJoin = 'area-left-join-data';
        $this->joinColumn = $joinColumnOnDataSide;
        $this->referenceValueToInclude = $referenceValueToInclude;
        return $this;
    }

    public function lastlyAreaRightJoinData(string $joinColumnOnDataSide = 'area_code', ?string $referenceValueToInclude = null): self
    {
        $this->leftJoin = 'data-left-join-area';
        $this->joinColumn = $joinColumnOnDataSide;
        $this->referenceValueToInclude = $referenceValueToInclude;
        return $this;
    }
    public function lastlyAreaCrossJoinData(string $secondaryJoinColumnOnDataSide,array $secondaryDataToJoin,string $joinColumnOnDataSide = 'area_code',?string $referenceValueToInclude = null,): self{
        $this->leftJoin = 'area-cross-join-data';
        $this->joinColumn = $joinColumnOnDataSide;
        $this->secondaryJoinColumn = $secondaryJoinColumnOnDataSide;
        $this->secondaryDataToJoin = $secondaryDataToJoin;
        $this->referenceValueToInclude = $referenceValueToInclude;
        return $this;
    }
    public function getCallingClassName($level)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        // level 0 is this method (immediate caller), 1 is this class (BreakoutQueryBuilder) and 2 is the dashboard artefact
        return $trace[$level]['class'] ?? null;
    }

    public function xRay($sql, $queryResult, $joinType, $finalResult)
    {
        Log::channel('x-ray')->info(json_encode([
                'name' => $this->getCallingClassName(3),
                'sql' => str($sql)->squish()->toString(),
                'queryResult' => $queryResult,
                'joinType' => $joinType,
                'finalResult' => $finalResult,
            ]) . '\n');
    }

    public function get($sql = null) : Collection
    {
        $query = '';
        $result = collect();
        $finalResult = collect();

        try {
            $query = $sql ?? $this->toSql();
            $result = collect($this->dbConnection->select($query));
            if ($this->leftJoin === 'area-left-join-data') {
                $finalResult = $this->areaLeftJoinData($result, $this->referenceValueToInclude);
            } elseif ($this->leftJoin === 'data-left-join-area') {
                $finalResult = $this->areaRightJoinData($result, $this->referenceValueToInclude);
            } elseif ($this->leftJoin === 'area-cross-join-data') {
                $finalResult = $this->areaCrossJoinData($result, $this->referenceValueToInclude, $this->secondaryDataToJoin, $this->secondaryJoinColumn);
            } else {
                $finalResult = $result;
            }
        } catch (\Exception $exception) {
            logger('From ' . $this->getCallingClassName(2) . ' in BreakoutQueryBuilder', ['Exception' => $exception->getMessage(), 'Line' => $exception->getLine()]);
        } finally {
            if (Context::hasHidden('x-ray')) {
                $this->xRay($query, $result, $this->leftJoin, $finalResult);
            }
        }
        return $finalResult;
    }


}
