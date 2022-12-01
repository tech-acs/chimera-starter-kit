<?php

namespace App\Http\Livewire;

use App\Models\MapIndicator;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Map extends Component
{
    public array $mapOptions;
    public array $indicators = [];
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

    protected function getGeoJson(array $paths, int $zoomDirection)
    {
        if ($zoomDirection >= 0) {
            $lqueryArray = "ARRAY[" . collect($paths)->map(fn ($path) => "'{$path}.*{1}'")->join(', ') . "]::lquery[]";
            $whereClause = empty($paths) ? "level = 0" : "path ?? $lqueryArray";
        } else {
            $lqueryArray = "ARRAY[" .
                collect($paths)
                    ->map(fn ($path) => str($path)->beforeLast('.')->toString())
                    ->unique()
                    ->map(fn ($x) => "'{$x}'")
                    ->join(', ') .
                "]::lquery[]";
            $whereClause = empty($paths) ? "level = 0" : "path ?? $lqueryArray";
        }
        //logger('getGeoJson', ['lqueryArray' => $lqueryArray, 'direction' => $zoomDirection]);
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

    public function setSelectedIndicator(string $mapIndicator)
    {
        $this->selectedIndicator = $mapIndicator;
        $selectedIndicator = new $this->selectedIndicator;
        $this->emit('indicatorSwitched', $selectedIndicator->getData(), $selectedIndicator->getStyles(), $selectedIndicator->getLegend());
    }

    final public function updateMap(int $level = 0, int $zoomDirection = 0, array $paths = [])
    {
        //dd($level, $zoomDirection, $paths);
        $defaultIndicator = null;
        if (! isset($this->selectedIndicator) && ! empty($this->indicators)) {
            $this->setSelectedIndicator(array_key_first($this->indicators));
            $defaultIndicator = new $this->selectedIndicator;
        }
        $geojson = json_decode($this->getGeoJson($paths, $zoomDirection));
        $filtered = collect($geojson->features)->filter(fn ($feature) => ! in_array($feature->properties->path, $this->previouslySentPaths));
        $filteredPaths = $filtered->map(fn ($feature) => $feature->properties->path)->all();
        $this->previouslySentPaths = array_merge($this->previouslySentPaths, $filteredPaths);
        $geojson->features = $filtered->values()->all();
        $this->emit('geojsonUpdated', $geojson, $level, $defaultIndicator?->getData() ?? []);
    }

    final public function mount()
    {
        $this->mapOptions = [
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
    }

    public function render()
    {
        return view('livewire.map');
    }
}
