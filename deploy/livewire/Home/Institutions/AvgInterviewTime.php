<?php

namespace App\Http\Livewire\Home\Institutions;

use App\Http\Livewire\Chart;
use App\Services\QueryBuilder;

class AvgInterviewTime extends Chart
{
    public function getCollection(array $filter)
    {
        $l = (new QueryBuilder($this->connection))
            ->select(['AVG(i_metadata_record.i_end_interview_time - i_metadata_record.i_start_interview_time) AS avg'])
            ->from(['i_metadata_record'])
            ->where(['i_metadata_record.i_start_interview_time IS NOT NULL', 'i_metadata_record.i_end_interview_time IS NOT NULL'])
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
