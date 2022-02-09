<?php

namespace App\Http\Livewire\Home;

use App\Http\Livewire\Chart;
use App\Services\Interfaces\BarChart;
use App\Services\Interfaces\LineChart;
use App\Services\QueryBuilder;
use App\Services\QueryFragmentFactory;
use Carbon\Carbon;

class Listing extends Chart implements LineChart, BarChart
{
    protected $listeners = ['updateChart' => 'update'];

    public function getCollection(array $filter)
    {
        list(, $whereConditions) = QueryFragmentFactory::make($this->connection)->getSqlFragments($filter);
        return (new QueryBuilder($this->connection))
            ->select(["STR_TO_DATE(SUBSTRING(LPAD(metadata_record.hh_program_publish_date, 14, '0'),1,8), '%Y%m%d') 
            AS enumeration_date", 'COUNT(metadata_record.hh_supervisor_code) AS total'])
            ->from(['metadata_record'])
            ->where($whereConditions)
            ->groupBy(['enumeration_date'])
            ->orderBy(['enumeration_date'])
            ->get();
    }

    protected function setData(array $filter = [])
    {
        $result = $this->getData($filter);
        $this->setNoData($result);
        $traceDaily = array_merge(
            $this::BarTraceTemplate,
            [
                'x' => $result->pluck('enumeration_date')->all(),
                'y' => $result->pluck('total')->all(),
                'text' => $result->pluck('total')->all(),
                'name' => 'Households',
            ]
        );
        $this->data = json_encode([$traceDaily]);
    }

    protected function setLayout(array $filter = [])
    {
        if ($this->noData) {
            $layout = $this->getEmptyLayoutArray();
        } else {
            $layout = $this->getLayoutArray();
            $layout['xaxis']['title']['text'] = "enumeration dates";
            $layout['yaxis']['title']['text'] = "Total households";

            $dates = config("chimera.dictionaries.{$this->connection}");
            $dates = [
                'start_date' => Carbon::parse($dates['start_date'])->subDays(3)->format('Y-m-d'),
                'end_date' => Carbon::parse($dates['end_date'])->addDays(3)->format('Y-m-d'),
            ];
            $layout['xaxis']['type'] = 'date';
            $layout['xaxis']['range'] = [$dates['start_date'], $dates['end_date']];
            $layout['xaxis']['rangeselector']['buttons'] = [['step' => 'all', 'label' => 'Show all']];
            $layout['xaxis']['rangeselector']['x'] = 0.9;
        }
        $this->layout = json_encode($layout);
    }

    public function render()
    {
        return view('livewire.home');
    }
}
