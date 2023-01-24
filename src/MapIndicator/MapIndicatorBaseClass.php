<?php

namespace Uneca\Chimera\MapIndicator;

use Illuminate\Support\Collection;
use Uneca\Chimera\Models\MapIndicator;

abstract class MapIndicatorBaseClass
{
    const DRAWING_OPTIONS = [ // https://leafletjs.com/reference.html#polyline-option
        'stroke'                => true,
        'color'	                => '#ccc',
        'weight'                => 1,
        'opacity'	            => 1,
        'lineCap'	            => 'round',
        'lineJoin'	            => 'round',
        'dashArray'	            => null,
        'dashOffset'            => null,
        'fill'	                => true,
        'fillColor'	            => '#ccc',
        'fillOpacity'           => 0.2,
        'fillRule'	            => 'evenodd',
        'bubblingMouseEvents'   => false,
    ];
    const COLOR_CHARTS = [
        'alizarin' => [
            '#fdedec',
            '#fadbd8',
            '#f5b7b1',
            '#f1948a',
            '#ec7063',
            '#e74c3c',
            '#cb4335',
            '#b03a2e',
            '#943126',
            '#78281f',
        ],
        'wisteria' => [
            '#f4ecf7',
            '#e8daef',
            '#d2b4de',
            '#bb8fce',
            '#a569bd',
            '#8e44ad',
            '#7d3c98',
            '#6c3483',
            '#5b2c6f',
            '#4a235a',
        ],
        'peter-river' => [
            '#ebf5fb',
            '#d6eaf8',
            '#aed6f1',
            '#85c1e9',
            '#5dade2',
            '#3498db',
            '#2e86c1',
            '#2874a6',
            '#21618c',
            '#1b4f72',
        ],
        'nephritis' => [
            '#e9f7ef',
            '#d4efdf',
            '#a9dfbf',
            '#7dcea0',
            '#52be80',
            '#27ae60',
            '#229954',
            '#1e8449',
            '#196f3d',
            '#145a32',
        ],
        'sunflower' => [
            '#fef9e7',
            '#fcf3cf',
            '#f9e79f',
            '#f7dc6f',
            '#f4d03f',
            '#f1c40f',
            '#d4ac0d',
            '#b7950b',
            '#9a7d0a',
            '#7d6608',
        ],
        'pumpkin' => [
            '#fbeee6',
            '#f6ddcc',
            '#edbb99',
            '#e59866',
            '#dc7633',
            '#d35400',
            '#ba4a00',
            '#a04000',
            '#873600',
            '#6e2c00',
        ],
        'silver' => [
            '#f8f9f9',
            '#f2f3f4',
            '#e5e7e9',
            '#d7dbdd',
            '#cacfd2',
            '#bdc3c7',
            '#a6acaf',
            '#909497',
            '#797d7f',
            '#626567',
        ],
        'rag' => ['red', 'orange', 'green'],
    ];
    const SELECTED_COLOR_CHART = 'nephritis';

    public MapIndicator $mapIndicator;
    public array $bins = [];
    public array $ranges = [];
    public array $currentStyle = [];
    public string $valueColumn = 'total';

    public function __construct()
    {
        $modelName = str($this::class)->after("App\MapIndicators" . '\\')->replace('\\', '/')->toString();
        $this->mapIndicator = MapIndicator::where('name', $modelName)->first();
        if (isset($this->bins)) {
            $this->ranges = $this->generateRanges($this->bins);
        }

        if (empty($this->ranges)) {
            dd('In ' . $this::class . ', you have not provided bins (to make ranges).');
        }

        $this->currentStyle = $this->getStyles();
        if (count($this->ranges) > count($this->currentStyle)) {
            dd('In ' . $this::class . ', you have more ranges (' . count($this->ranges) . ') than you have styles (' . count($this->currentStyle) . '). You need to have enough styles defined for your ranges.');
        }
    }

    protected function generateRanges(array $bins)
    {
        $ranges = [];
        for ($i = 0; $i < count($bins) - 1; $i++) {
            array_push($ranges, [$bins[$i], $bins[$i + 1]]);
        }
        return $ranges;
    }

    public function assignStyle($value)
    {
        $styles = array_keys($this->currentStyle);
        if ($value < $this->ranges[0][0]) { // Below range -> first style
            return array_key_first($this->currentStyle);
        }
        foreach ($this->ranges as $index => $range) { // Within range -> corrosponding style
            if (($value >= $range[0]) && ($value < $range[1])) {
                return $styles[$index];
            }
        }
        if ($value >= end($this->ranges)[1]) { // Above range -> last style
            return array_key_last($this->currentStyle);
        }
        return 'default';
    }

    public function getData(array $filter): Collection
    {
        return collect([]);
    }

    public function getLegend(): array
    {
        if (! empty($this->ranges)) {
            return collect($this->currentStyle)
                ->take(count($this->ranges))
                ->map(fn ($style) => $style['fillColor'])
                ->combine($this->ranges)
                ->map(fn ($rangeArray) => implode(' - ', $rangeArray))
                ->all();
        }
        return [];
    }

    public function getStyles(): array
    {
         $styles = collect($this::COLOR_CHARTS)
            ->map(function ($chart, $name) {
                return collect($chart)->mapWithKeys(function ($color, $index) use ($name) {
                    return ["$name-$index" => array_merge(self::DRAWING_OPTIONS, ['fillColor' => $color])];
                })->all();
            })
            ->all();
         return $styles[$this::SELECTED_COLOR_CHART];
    }
}
