<?php

namespace Uneca\Chimera\Http\Livewire;

use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\MapIndicator\MapIndicatorBaseClass;
use Uneca\Chimera\Models\MapIndicator;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Uneca\Chimera\Services\MapIndicatorCaching;

class Map extends Component
{
    public array $leafletMapOptions;
    public array $indicators = [];
    public array $levelToZoomMapping;
    public string $selectedIndicator;
    public array $previouslySentPaths = [];

    protected function getListeners()
    {
        return [
            'mapReady' => 'updateMap',
            'mapMoved' => 'updateMap',
            'indicatorSelected' => 'setSelectedIndicator'
        ];
    }

    protected function deriveNextPathsBasedOnZoomDirection(array $previousPaths, int $zoomDirection): Collection
    {
        if ($zoomDirection >= 0) {
            return collect($previousPaths)
                ->map(fn ($path) => "'{$path}.*{1}'");
        } else {
            return collect($previousPaths)
                ->map(fn ($path) => str($path)->beforeLast('.')->toString())
                ->unique()
                ->map(fn ($path) => "'{$path}'");
        }
    }

    protected function getGeoJson(Collection $derivedPaths)
    {
        $lqueryArray = "ARRAY[" . $derivedPaths->join(', ') . "]::lquery[]";
        $whereClause = $derivedPaths->isEmpty() ? "level = 0" : "path ?? $lqueryArray";
        $sql = "
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(
                    json_build_object(
                        'type',       'Feature',
                        'geometry',   ST_AsGeoJSON(ST_GeomFromWKB(filtered_areas.geom))::json,
                        'properties', json_build_object(
                            'code', code,
                            'name', name,
                            'level', level,
                            'path', path,
                            'style', 'default'
                        )
                    )
                )
            ) AS feature_collection
            FROM
            (
                SELECT name, code, level, path, geom
                FROM areas
                WHERE $whereClause
            ) AS filtered_areas
        ";
        try {
            $result = DB::select($sql);
        } catch (Exception $exception) {
            logger('Query error in getGeoJson()', ['exception' => $exception->getMessage()]);
            return '';
        }
        return $result[0]->feature_collection;
    }

    private function getDataAndCacheIt(?MapIndicatorBaseClass $mapIndicator, int $level, array $paths): array
    {
        if (config('chimera.cache.enabled')) {
            return $mapIndicator?->getData($level, $paths) ?? [];

            /*$key = 'map-indicator|' . $mapIndicator->model->slug . implode('-', array_filter($paths));
            //$this->dataTimestamp = $caching->getTimestamp();
            //logger($caching->key, ['Is cached?' => Cache::tags($caching->tags())->has($caching->key)]);
            return Cache::tags([$mapIndicator->model->questionnaire, 'map-indicators'])
                ->rememberForever($key, function () use ($mapIndicator, $level, $paths) {
                    //$caching->stamp();
                    return $mapIndicator->getData($level, $paths);
                });*/
        }
        return $mapIndicator?->getData($level, $paths) ?? [];
    }

    public function setSelectedIndicator(string $mapIndicator, int $level)
    {
        $this->selectedIndicator = $mapIndicator;
        $selectedIndicator = new $this->selectedIndicator;
        $this->emit('indicatorSwitched', $this->getDataAndCacheIt($selectedIndicator, $level, []), $selectedIndicator->getStyles(), $selectedIndicator->getLegend());
    }

    final public function updateMap(int $level = 0, int $zoomDirection = 0, array $paths = [])
    {
        $derivedPaths = $this->deriveNextPathsBasedOnZoomDirection($paths, $zoomDirection);
        $currentIndicator = null;
        if (! isset($this->selectedIndicator) && ! empty($this->indicators)) {
            $this->setSelectedIndicator(array_key_first($this->indicators), $level);
            $currentIndicator = new $this->selectedIndicator;
        }

        $geojson = json_decode($this->getGeoJson($derivedPaths));
        $filtered = collect($geojson->features)->filter(fn ($feature) => ! in_array($feature->properties->path, $this->previouslySentPaths));
        $filteredPaths = $filtered->map(fn ($feature) => $feature->properties->path)->all();
        $this->previouslySentPaths = array_merge($this->previouslySentPaths, $filteredPaths);
        $geojson->features = $filtered->values()->all();
        //dump($geojson, $filteredPaths, $this->previouslySentPaths);
        $this->emit('geojsonUpdated', $geojson, $level, $this->getDataAndCacheIt($currentIndicator, $level, $derivedPaths->all()) ?? []);
    }

    final public function mount()
    {
        $this->leafletMapOptions = [
            'center' => config('chimera.area.map.center'),
            'zoom' => 6,
            'zoomControl' => false,
            'attributionControl' => false,
            'preferCanvas' => true,
            'locale' => app()->getLocale(),
        ];
        $this->indicators = MapIndicator::published()
            ->get()
            ->mapWithKeys(fn ($indicator) => [$indicator->fully_qualified_classname => $indicator->title])
            ->all();
        $this->levelToZoomMapping = config('chimera.area.map.level_to_zoom_mapping');
    }

    public function render()
    {
        return view('chimera::livewire.map');
    }
}
