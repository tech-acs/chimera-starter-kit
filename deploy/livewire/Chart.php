<?php

namespace App\Http\Livewire;

use App\Services\Caching;
use App\Services\Traits\Cachable;
use Livewire\Component;

abstract class Chart extends Component
{
    use Cachable;

    public string $graphDiv;
    public string $data;
    public string $layout;
    public string $config;
    public string $connection;
    public bool $noData = false;
    public string $dataTimestamp;
    public string $help;

    protected function getLayoutArray(): array
    {
        return [
            'height' => 450,
            'title' => [],
            'showlegend' => true,
            'legend' => ['orientation' => 'h', 'x' => 0, 'y' => 1.12],
            'xaxis' => [
                'type' => 'category',
                'tickmode' => 'linear',
                'automargin' => true,
                'title' => [
                    'text' => ''
                ],
            ],
            'yaxis' => [
                'title' => ['text' => '']
            ],
            'margin' => ['l' => 60, 'r' => 10, 't' => 10, 'b' => 40],
            'modebar' => ['orientation' => 'v', 'color' => 'white', 'bgcolor' => 'darkgray'],
            'dragmode' => 'pan',
            'colorway' => ['#0abab5', '#d2b48c', '#f28500', '#45b08c'],
            'separators' => '.,',
        ];
    }

    protected function getThresholdColors(array $expected, array $actual)
    {
        return collect($expected)
            ->zip($actual)
            ->map(function ($pair) {
                list($expected, $actual) = $pair;
                $green = [$expected * 0.95, $expected * 1.05];
                $amber = [$expected * 0.90, $expected * 1.1];
                if (($green[0] <= $actual) && ($actual <= $green[1])) {
                    return 'green';
                } elseif (($amber[0] <= $actual) && ($actual <= $amber[1])) {
                    return '#FFBF00'; // Amber
                } else {
                    return 'red';
                }
            })->all();
    }

    protected function getEmptyLayoutArray(): array
    {
        $layout = $this->getLayoutArray();
        $layout['xaxis']['visible'] = false;
        $layout['yaxis']['visible'] = false;
        $layout['annotations'] = [[
            "text" => "There is no data for this chart at this level",
            "xref" => "paper",
            "yref" => "paper",
            "showarrow" => False,
            "font" => [
                "size" => 28
            ]
        ]];
        return $layout;
    }

    protected function setNoData($result)
    {
        if ($result->count() > 0) {
            $this->noData = false;
        } else {
            $this->noData = true;
        }
    }

    protected function setConfig(array $config)
    {
        $this->config = json_encode($config);
    }

    public static function getXAxisTitle($filter)
    {
        if (!blank($filter['constituency'] ?? null)) {
            $xAxisTitle = "EAs of {$filter['constituencyName']} constituency";
        } elseif (!blank($filter['region'] ?? null)) {
            $xAxisTitle = "Constituencies of {$filter['regionName']} region";
        } else {
            $xAxisTitle = 'Regions';
        }
        return $xAxisTitle;
    }

    public static function getCurrentAreaTitle($filter)
    {
        if (!blank($filter['constituency'] ?? null)) {
            $xAxisTitle = "of {$filter['constituencyName']} constituency";
        } elseif (!blank($filter['region'] ?? null)) {
            $xAxisTitle = "of {$filter['regionName']} region";
        } else {
            $xAxisTitle = '';
        }
        return $xAxisTitle;
    }

    protected function setLayout(array $filter = [])
    {
        $this->layout = json_encode($this->getLayoutArray());
    }

    public function mount()
    {
        $this->setConfig([
            'responsive' => true,
            'displaylogo' => false,
            'modeBarButtonsToRemove' => ['select2d', 'lasso2d', 'autoScale2d', 'hoverClosestCartesian', 'hoverCompareCartesian'],
            'toImageButtonOptions' => ['filename' => $this->graphDiv,],
        ]);
        $filtersToApply = array_merge(
            auth()->user()->areaFilter($this->connection),
            session()->get($this->connection, [])
        );
        $this->setData($filtersToApply);
        $key = Caching::makeIndicatorCacheKey($this->graphDiv, $filtersToApply);
        //$this->dataTimestamp = Cache::tags([$this->connection, 'timestamp'])->get($key, 'Unknown');
        $this->setLayout($filtersToApply);
    }

    public function render()
    {
        return view('livewire.chart');
    }
}
