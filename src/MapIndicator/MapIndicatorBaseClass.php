<?php

namespace Uneca\Chimera\MapIndicator;

use Uneca\Chimera\Models\MapIndicator;
use Illuminate\Support\Collection;

abstract class MapIndicatorBaseClass
{
    const DRAWING_OPTIONS = [ // https://leafletjs.com/reference.html#polyline-option
        'stroke'                => true,
        'color'	                => '#3388ff',
        'weight'                => 1,
        'opacity'	            => 1,
        'lineCap'	            => 'round',
        'lineJoin'	            => 'round',
        'dashArray'	            => null,
        'dashOffset'            => null,
        'fill'	                => true,
        'fillColor'	            => '#3388ff',
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
        'rag' => ['red', 'amber', 'green'],
    ];
    const DEFAULT_STYLE = 'nephritis';

    protected string $questionnaire;
    protected array $bins = [];
    protected array $ranges = [];

    public function __construct()
    {
        $modelName = str($this::class)->after("App\MapIndicators" . '\\')->replace('\\', '/')->toString();
        $this->questionnaire = MapIndicator::where('name', $modelName)->first()->questionnaire;
        if (isset($this->bins)) {
            $this->ranges = $this->generateRanges($this->bins);
        }

        if (count($this->ranges) > count($this->getStyles())) {
            dd('In ' . $this::class . ', you have more ranges than you have styles. You need to have enough styles defined for you ranges.');
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

    protected function assignedStyle($value)
    {
        $styles = array_keys($this->getStyles());
        foreach ($this->ranges as $index => $range) {
            if (($value >= $range[0]) && ($value < $range[1])) {
                return $styles[$index];
            }
        }
        return 'default';
    }

    public function getData(int $level, array $paths): array
    {
        return [];
    }

    public function getLegend(): array
    {
        if (! empty($this->ranges)) {
            return collect($this->getStyles())
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
         return $styles[$this::DEFAULT_STYLE];
    }

    public function styleClassifier($value): string
    {
        $colors = array_values($this->getStyles());
        foreach ($this->ranges as $index => $range) {
            if ($value > $range[0] && $value <= $range[1]) {
                $assignedStyle = $colors[$index];
                break;
            }
        }
    }
}
