<?php

namespace App\Http\Livewire\Home\Households;

use App\Http\Livewire\Chart;
use App\Services\QueryBuilder;

class TotalPopulation extends Chart
{
    public function getCollection(array $filter)
    {
        $l = (new QueryBuilder($this->connection, false))
            ->select(['SUM(household_characters_details.system_total_count) AS total'])
            ->from(['household_characters_details'])
            ->get()
            ->first();

        if (!is_null($l)) {
            return number_format($l->total, 0, '', ',');
        }
        return 0;
    }

    protected function setData(array $filter = [])
    {
        // TODO: Implement setData() method.
    }
}
