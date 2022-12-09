<?php

namespace Uneca\Chimera\MapIndicator;

use Uneca\Chimera\Models\MapIndicator;
use Illuminate\Support\Collection;

abstract class MapIndicatorBaseClass
{
    const DEFAULT_STYLE = [ // https://leafletjs.com/reference.html#polyline-option
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

    public function getData(int $level, Collection $paths = null): array
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
        return [
            'red' => array_merge(self::DEFAULT_STYLE, ['color' => 'red', 'fillColor' => 'red']),
            'amber' => array_merge(self::DEFAULT_STYLE, ['color' => 'orange', 'fillColor' => 'orange']),
            'green' => array_merge(self::DEFAULT_STYLE, ['color' => 'green', 'fillColor' => 'green']),
            'default' => self::DEFAULT_STYLE,
        ];
    }
}
