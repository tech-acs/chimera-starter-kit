<?php

namespace App\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Map extends Component
{
    public array $styles;
    public array $mapOptions;
    public array $previouslySentPaths = [];

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

    protected function getListeners()
    {
        return [
            'mapReady' => 'updateMap',
            'mapMoved' => 'updateMap',
        ];
    }

    protected function getGeoJson(array $paths, int $level, int $zoomDirection)
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

    public function getData(array $paths = [])
    {
        return
        [
            'population' => [
                '01' => ['value' => 494, 'style' => 'amber'],
                '02' => ['value' => 259, 'style' => 'red'],
                '03' => ['value' => 682, 'style' => 'green'],
                '04' => ['value' => 179, 'style' => 'red'],
                '05' => ['value' => 842, 'style' => 'green'],
                '06' => ['value' => 642, 'style' => 'green'],
                '07' => ['value' => 591, 'style' => 'green'],
                '08' => ['value' => 493, 'style' => 'amber'],
                '09' => ['value' => 731, 'style' => 'green'],
                '10' => ['value' => 225, 'style' => 'red'],
                '11' => ['value' => 742, 'style' => 'green'],
            ],
            'households' => [
                '01' => ['value' => 259, 'style' => 'amber'],
                '02' => ['value' => 842, 'style' => 'green'],
                '03' => ['value' => 682, 'style' => 'green'],
                '04' => ['value' => 179, 'style' => 'red'],
                '05' => ['value' => 442, 'style' => 'amber'],
                '06' => ['value' => 642, 'style' => 'green'],
                '07' => ['value' => 591, 'style' => 'green'],
                '08' => ['value' => 193, 'style' => 'red'],
                '09' => ['value' => 231, 'style' => 'red'],
                '10' => ['value' => 325, 'style' => 'amber'],
                '11' => ['value' => 749, 'style' => 'green'],
            ]
        ];
        //return (new Population('households'))->getData();
    }

    final public function updateMap(int $level, int $zoomDirection = 0, array $paths = [])
    {
        $geojson = json_decode($this->getGeoJson($paths, $level, $zoomDirection));
        /*$level = empty($paths) ? 0 : str($paths[0])->explode('.')->count();
        $level = $zoomDirection < 0 ? $level - 2 : $level;*/
        //logger("Hmm", ['dir' => $zoomDirection, 'lev' => $level]);

        $filtered = collect($geojson->features)->filter(fn ($feature) => ! in_array($feature->properties->path, $this->previouslySentPaths));

        $filteredPaths = $filtered->map(fn ($feature) => $feature->properties->path)->all();
        $this->previouslySentPaths = array_merge($this->previouslySentPaths, $filteredPaths);
        /*logger('Filtered', [
            'features' => collect($geojson->features)->map(fn ($feature) => $feature->properties->path)->all(),
            'previouslySent' => $this->previouslySentPaths,
            'filtered' => $filteredPaths
        ]);*/
        $geojson->features = $filtered->values()->all();
        $this->emit('geojsonUpdated', $geojson, $level, $this->getData());
    }

    final public function mount()
    {
        $this->mapOptions = [
            'center' => config('chimera.area.map.center'),
            'zoom' => 6,
            'zoomControl' => false,
            'attributionControl' => false,
            'preferCanvas' => true,
        ];
        $this->styles = [
            'default' => self::DEFAULT_STYLE,
            'red' => array_merge(self::DEFAULT_STYLE, ['color' => 'red', 'fillColor' => 'red']),
            'amber' => array_merge(self::DEFAULT_STYLE, ['color' => 'orange', 'fillColor' => 'orange']),
            'green' => array_merge(self::DEFAULT_STYLE, ['color' => 'green', 'fillColor' => 'green'])
        ];
    }

    public function render()
    {
        return view('livewire.map');
    }
}
