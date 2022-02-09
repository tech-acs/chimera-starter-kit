<?php

namespace App\Http\Livewire\Home\Households;

use App\Http\Livewire\Chart;
use App\Services\QueryBuilder;

class AvgHouseholdSize extends Chart
{
    public function getCollection(array $filter)
    {
        $l = (new QueryBuilder($this->connection, false))
            ->select(['SUM(household_characters_details.system_total_count) AS total_population',
                    'COUNT(hh_hh) AS total_households'])
            ->from(['household_characters_details'])
            ->get()
            ->first();

        if (!is_null($l) && $l->total_households != 0) {
            return number_format($l->total_population/$l->total_households, 1, '.', ',');
        }
        return 0;
    }

    protected function setData(array $filter = [])
    {
        // TODO: Implement setData() method.
    }
}
