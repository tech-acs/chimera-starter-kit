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
    protected array $tables;
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
        list($selectColumns, $whereConditions, $concernedTables) = QueryFragmentFactory::make($dataSource)->getSqlFragments($filter);

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
