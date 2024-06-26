<?php

namespace Uneca\Chimera\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        string $dataSource = null,
        string $filterPath = '',
        bool $excludePartials = true,
        bool $excludeDeleted = true,
        string $partialCaseIdentifyingCondition = 'cases.partial_save_mode is NULL'
    )
    {
        $this->filterPath = $filterPath;
        //$filter = AreaTree::pathAsFilter($filterPath);
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

    private function areaLeftJoinData(Collection $data, ?string $referenceValueToInclude): Collection
    {
        if ($data->isEmpty()) {
            return $data;
        }
        $areas = (new AreaTree())->areas($this->filterPath, referenceValueToInclude: $referenceValueToInclude);
        //logger('area wt', ['areas' => $areas]);
        $dataKeyByAreaCode = $data->keyBy($this->joinColumn);
        $columns = array_keys((array) $data[0]);
        return $areas->map(function ($area) use ($dataKeyByAreaCode, $columns) {
            foreach ($columns as $column) {
                if ($column != $this->joinColumn) {
                    $area->{$column} = $dataKeyByAreaCode[$area->code]->{$column} ?? 0;
                }
            }
            return $area;
        });
    }

    private function areaRightJoinData(Collection $data, ?string $referenceValueToInclude): Collection
    {
        if ($data->isEmpty()) {
            return $data;
        }
        $areas = (new AreaTree())->areas($this->filterPath, referenceValueToInclude: $referenceValueToInclude);
        $areasKeyByAreaCode = $areas->pluck('name', 'code');
        return $data->map(function ($row) use ($areasKeyByAreaCode) {
            $row->area_name = $areasKeyByAreaCode[$row->{$this->joinColumn}];
            return $row;
        });
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

    public function get($sql = null) : Collection
    {
        try {
            $data = collect($this->dbConnection->select($sql ?? $this->toSql()));
            if ($this->leftJoin === 'area-left-join-data') {
                $data = $this->areaLeftJoinData($data, $this->referenceValueToInclude);
            } elseif ($this->leftJoin === 'data-left-join-area') {
                $data = $this->areaRightJoinData($data, $this->referenceValueToInclude);
            }
            return $data;
        } catch (\Exception $exception) {
            logger('In BreakoutQueryBuilder', ['Exception' => $exception->getMessage()]);
            return collect([]);
        }
    }


}
