<?php

namespace App\MapIndicators;

use App\Models\MapIndicator;

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
    public string $questionnaire;

    public function __construct()
    {
        $modelName = str($this::class)->after(__NAMESPACE__ . '\\')->replace('\\', '/')->toString();
        $this->questionnaire = MapIndicator::where('name', $modelName)->first()->questionnaire;
    }

    public function getData(int $level = 0): array
    {
        return [];
    }

    public function getLegend(): array
    {
        return [];
    }

    public function getStyles(): array
    {
        return [
            'default' => self::DEFAULT_STYLE,
            'red' => array_merge(self::DEFAULT_STYLE, ['color' => 'red', 'fillColor' => 'red']),
            'amber' => array_merge(self::DEFAULT_STYLE, ['color' => 'orange', 'fillColor' => 'orange']),
            'green' => array_merge(self::DEFAULT_STYLE, ['color' => 'green', 'fillColor' => 'green'])
        ];
    }
}
