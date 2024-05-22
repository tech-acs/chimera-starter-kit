<?php

namespace Uneca\Chimera\Livewire;

use Carbon\Carbon;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\AreaTree;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Services\IndicatorCaching;

abstract class Chart extends Component
{
    public Indicator $indicator;
    public string $graphDiv;
    public array $data;
    public array $layout;
    public array $config;
    public Carbon $dataTimestamp;
    public bool $isBeingFeatured = false;

    const DEFAULT_CONFIG = [
        'responsive' => true,
        'displaylogo' => false,
        'modeBarButtonsToRemove' => ['select2d', 'lasso2d', 'autoScale2d', 'hoverClosestCartesian', 'hoverCompareCartesian'],
    ];
    const DEFAULT_LAYOUT = [
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
            'title' => ['text' => ''],
        ],
        'margin' => ['l' => 60, 'r' => 10, 't' => 10, 'b' => 40],
        'modebar' => ['orientation' => 'v', 'color' => 'white', 'bgcolor' => 'darkgray'],
        'dragmode' => 'pan',
    ];
    const EMPTY_CHART_LAYOUT_DIFF = [
        'xaxis' => ['visible' => false],
        'yaxis' => ['visible' => false],
        'annotations' => [[
            'text' => 'There is no data for this chart at this level',
            'xref' => 'paper',
            'yref' => 'paper',
            'showarrow' => false,
            'font' => ['size' => 28]
        ]]
    ];

    protected function getListeners(): array
    {
        return ['filterChanged' => 'updateChart'];
    }

    protected function getConfig(): array
    {
        $dynamicOptions = [
            'toImageButtonOptions' => ['filename' => $this->graphDiv . ' (' . now()->toDayDateTimeString() . ')'],
            'locale' => app()->getLocale(),
        ];
        return array_merge(self::DEFAULT_CONFIG, $dynamicOptions);
    }

    public function getData(array $filter): Collection
    {
        return collect();
    }

    public function addAreaNames(Collection $data, string $filterPath, string $keyByColumn = 'area_code'): Collection
    {
        if ($data->isEmpty()) {
            return $data;
        }
        $areas = (new AreaTree())->areas($filterPath);
        $dataKeyByAreaCode = $data->keyBy($keyByColumn);
        $columns = array_keys((array) $data[0]);
        return $areas->map(function ($area) use ($dataKeyByAreaCode, $columns, $keyByColumn) {
            foreach ($columns as $column) {
                if ($column != $keyByColumn) {
                    $area->{$column} = $dataKeyByAreaCode[$area->code]->{$column} ?? 0;
                }
            }
            return $area;
        });
    }

    protected function getTraces(Collection $data, string $filterPath): array
    {
        return [];
    }

    protected function getLayout(string $filterPath): array
    {
        $currentPalette = ColorPalette::palette(settings('color_palette'));
        return [...self::DEFAULT_LAYOUT, 'colorway' => $currentPalette->colors];
    }

    protected function mounted(): void
    {
        // A lifecycle hook method to be used in derived classes, if needed
    }

    protected function isDataEmpty(): bool
    {
        return array_reduce($this->data, function ($carry, $trace) {
            /*
            * Not all graphs put their data under the 'x' and 'y' keys.
            * Pies for example put it in the 'values' key.
            * Therefore, you might need to add those other cases here!
            */
            return match ($trace['type'] ?? null) {
                'pie' => empty($trace['values']) && $carry,
                default => empty($trace['x']) && $carry,
            };
        }, true);
    }

    private function getDataAndCacheIt(array $filter): Collection
    {
        $analytics = ['user_id' => auth()->id(), 'source' => 'Cache', 'level' => empty($filter) ? null : (count($filter) - 1), 'started_at' => time(), 'completed_at' => null];
        $this->dataTimestamp = Carbon::now();
        try {
            if (config('chimera.cache.enabled')) {
                $caching = new IndicatorCaching($this->indicator, $filter);
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
            logger("Exception occurred while trying to cache (in Chart.php, getDataAndCacheIt method)", ['Exception: ' => $exception]);
            return collect([]);
        } finally {
            if ($analytics['source'] !== 'Cache') {
                $analytics['completed_at'] = time();
                $this->indicator->analytics()->create($analytics);
            }
        }
    }

    private function updateDataAndLayout(array $filter): void
    {
        $filterPath = AreaTree::getFinestResolutionFilterPath($filter);
        $this->data = $this->getTraces($this->getDataAndCacheIt(AreaTree::translatePathToCode($filter)), $filterPath);
        $this->layout = $this->getLayout($filterPath);

        if ($this->isDataEmpty()) {
            $this->layout = array_merge(self::DEFAULT_LAYOUT, self::EMPTY_CHART_LAYOUT_DIFF);
        }
    }

    public function updateChart(array $filter): void
    {
        $this->updateDataAndLayout($filter);
        $this->dispatch("redrawChart-{$this->graphDiv}", $this->data, $this->layout);
    }

    public function deferredLoading()
    {
        $filtersToApply = array_merge(
            auth()->user()->areaRestrictionAsFilter(),
            ($this->isBeingFeatured ? [] : session()->get('area-filter', []))
        );
        $this->updateChart($filtersToApply);
    }

    public function mount()
    {
        $this->graphDiv = $this->indicator->component;
        $this->config = $this->getConfig();
        $this->data = [];

        $this->mounted();
    }

    public function render()
    {
        return view('chimera::livewire.chart');
    }
}
