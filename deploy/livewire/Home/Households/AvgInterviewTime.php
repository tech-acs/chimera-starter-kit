<?php

namespace App\Http\Livewire\Home\Households;

use App\Http\Livewire\Chart;
use App\Services\QueryBuilder;

class AvgInterviewTime extends Chart
{
    public function getCollection(array $filter)
    {
        $l = (new QueryBuilder($this->connection))
            ->select(['AVG(metadata_record.hh_end_interview_time - metadata_record.hh_start_interview_time) AS avg'])
            ->from(['metadata_record'])
            ->where(['metadata_record.hh_start_interview_time IS NOT NULL', 'metadata_record.hh_end_interview_time IS NOT NULL'])
            ->get()
            ->first();

        if (!is_null($l)) {
            return number_format($l->avg/60, 0, '', ',');
        }
        return 0;
    }

    protected function setData(array $filter = [])
    {
        // TODO: Implement setData() method.
    }
}
