<?php

namespace App\Http\Livewire;

use App\Models\Indicator;
use App\Services\AreaTree;
use App\Services\Caching;
use App\Services\Traits\Cachable;
use Livewire\Component;

abstract class Chart extends Component
{
    use Cachable;

    public Indicator $indicator;
    public string $graphDiv;
    public array $data;
    public array $layout;
    public array $config;
    public string $connection;
    public bool $noData = false;
    public string $dataTimestamp;

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
                'title' => ['text' => ''],
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
            "font" => ["size" => 28]
        ]];
        return $layout;
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

    protected static function getFinestResolutionFilterPath(array $filter)
    {
        return array_reduce($filter, function ($carriedLongest, $path) {
            return strlen($path) >= strlen($carriedLongest) ? $path : $carriedLongest;
        }, '');
    }

    public static function getXAxisTitle($filter)
    {
        $areaTree = new AreaTree;
        $hierarchies = config('chimera.area.hierarchies');
        $path = self::getFinestResolutionFilterPath($filter);
        $depth = collect(explode('.', $path))->filter()->count();
        $levelName = $hierarchies[$depth];
        $title = str($levelName)->plural()->title();
        if ($depth > 0) {
            $previousLevel = $areaTree->getArea($path);
            $title .= " of " . $previousLevel->name . ' ' . $hierarchies[$previousLevel->level];
        }
        return $title;
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
        $this->config = $config;
    }

    protected function setLayout(array $filter = [])
    {
        $this->layout = $this->getLayoutArray();
    }

    public function mount()
    {
        $this->graphDiv = $this->indicator->component;
        $this->connection = $this->indicator->questionnaire;
        $this->help = $this->indicator->help;

        $this->setConfig([
            'responsive' => true,
            'displaylogo' => false,
            'modeBarButtonsToRemove' => ['select2d', 'lasso2d', 'autoScale2d', 'hoverClosestCartesian', 'hoverCompareCartesian'],
            'toImageButtonOptions' => ['filename' => $this->graphDiv . ' (' . now()->toDayDateTimeString() . ')',],
        ]);
        $filtersToApply = array_merge(
            auth()->user()->areaFilter($this->connection),
            session()->get('area-filter', [])
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
