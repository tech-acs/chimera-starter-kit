<?php

namespace Uneca\Chimera\MapIndicator;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\MapIndicatorCaching;

abstract class MapIndicatorBaseClass
{
    const LEAFLET_POLYLINE_OPTIONS = [ // https://leafletjs.com/reference.html#polyline-option
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
    const DEFAULT_STYLE = [
        ...self::LEAFLET_POLYLINE_OPTIONS,
        'color' => 'white',
        'fillColor' => 'dimgrey',
        'fillOpacity' => 0.65,
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

    public string $valueField = 'value';
    public string $displayValueField = 'display_value';
    public string $areaCodeField = 'area_code';
    public string $infoTextField = 'info';

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
        foreach ($this->ranges as $index => $range) { // Within range -> corresponding style
            if (($value >= $range[0]) && ($value < $range[1])) {
                return $styles[$index];
            }
        }
        if ($value >= end($this->ranges)[1]) { // Above range -> last style
            return array_key_last($this->currentStyle);
        }
        return 'default';
    }

    public function getDataAndCacheIt(string $path): Collection
    {
        //if (isset($this->currentIndicator)) {
            //$currentIndicator = new $this->currentIndicator;
            $filter = AreaTree::pathAsFilter($path);
            $areaRestriction = auth()->user()->areaRestrictionAsFilter();
            // Merge $filter and $areaRestriction

            $analytics = ['user_id' => auth()->id(), 'source' => 'Cache', 'level' => empty($filter) ? null : (count($filter) - 1), 'started_at' => time(), 'completed_at' => null];
            $this->dataTimestamp = Carbon::now();
            try {
                if (config('chimera.cache.enabled')) {
                    $caching = new MapIndicatorCaching($this->mapIndicator, $filter);
                    $this->dataTimestamp = $caching->getTimestamp();
                    return Cache::tags($caching->tags())
                        ->remember($caching->key, config('chimera.cache.ttl'), function () use ($caching, &$analytics) {
                            $caching->stamp();
                            $this->dataTimestamp = Carbon::now();
                            $analytics['source'] = 'Caching';
                            return $this->getData($caching->filter);
                        });
                }
                $analytics['source'] = 'Not caching';
                return $this->getData($filter);
            } catch (\Exception $exception) {
                logger("Exception occurred while trying to cache (in Map.php, getDataAndCacheIt method)", ['Exception: ' => $exception]);
                return collect([]);
            } finally {
                if ($analytics['source'] !== 'Cache') {
                    $analytics['completed_at'] = time();
                    $this->mapIndicator->analytics()->create($analytics);
                }
            }
        //}
        //return collect([]);
    }

    public function getData(array $filter): Collection
    {
        return collect([]);
    }

    public function getMappableData(Collection $data, string $filterPath): Collection
    {
        return $data->map(function ($row) {
            $row->value = $row->{$this->valueField};
            $row->display_value = $row->{$this->displayValueField} ?? null;
            $row->info = $row->{$this->infoTextField} ?? null;
            $row->style = $this->assignStyle($row->value);
            return $row;
        });
    }

    public function getLegend(): array
    {
        if (! empty($this->ranges)) {
            $legend = collect($this->currentStyle)
                ->take(count($this->ranges))
                ->map(fn ($style) => $style['fillColor'])
                ->combine($this->ranges)
                ->map(fn ($rangeArray) => implode(' - ', $rangeArray));
            $firstKey = $legend->keys()->first();
            $lastKey = $legend->keys()->last();
            $legend = $legend->replace([
                $firstKey => str($legend[$firstKey])->after('-')->trim()->prepend('< ')->toString(),
                $lastKey => str($legend[$lastKey])->before('-')->trim()->prepend('> ')->toString()
            ]);
            return $legend
                ->merge(['dimgray' => __('No data')])
                ->all();
        }
        return [];
    }

    public function getStyles(): array
    {
         $styles = collect($this::COLOR_CHARTS)
            ->map(function ($chart, $name) {
                return collect($chart)->mapWithKeys(function ($color, $index) use ($name) {
                    return ["$name-$index" => array_merge(self::DEFAULT_STYLE, ['fillColor' => $color])];
                })->all();
            })
            ->all();
         return $styles[$this::SELECTED_COLOR_CHART];
    }
}
