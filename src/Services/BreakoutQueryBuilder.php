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
    private string $select;
    protected array $columns;
    private string $from;
    private string $where;
    protected array $conditions;
    private string $groupBy;
    private string $having;
    private string $orderBy;

    public function __construct(
        string $dataSource = null,
        array $filter = [],
        bool $excludePartials = true,
        bool $excludeDeleted = true,
        string $partialCaseIdentifyingCondition = 'cases.partial_save_mode is NULL'
    )
    {
        list($selectColumns, $whereConditions) = QueryFragmentFactory::make($dataSource)->getSqlFragments($filter);

        $this->dbConnection = DB::connection($dataSource);
        $this->excludePartials = $excludePartials;
        $this->partialCaseIdentifyingCondition = $partialCaseIdentifyingCondition;
        $this->excludeDeleted = $excludeDeleted;
        $this->select = '';
        $this->columns = $selectColumns;
        $this->from = '';
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
        foreach ($items as $item) {
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

    public function toSql() : string
    {
        if (count($this->conditions) > 0) {
            $this->where = "WHERE " . implode(' AND ', $this->conditions);
        }
        return "{$this->select} {$this->from} {$this->where} {$this->groupBy} {$this->having} {$this->orderBy}";
    }

    public function get($sql = null) : Collection
    {
        try {
            return collect($this->dbConnection->select($sql ?? $this->toSql()));
        } catch (\Exception $exception) {
            logger('In BreakoutQueryBuilder', ['Exception' => $exception->getMessage()]);
            return collect([]);
        }
    }
}
