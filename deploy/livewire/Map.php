<?php

namespace App\Http\Livewire;

use App\MapIndicators\Population;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Map extends Component
{
    public string $geojson;
    public array $styles;
    public array $mapOptions;

    const DEFAULT_STYLE = [ // https://leafletjs.com/reference.html#polyline-option
        'stroke'                => true,
        'color'	                => '#3388ff',
        'weight'                => 3,
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
        'className'             => 'border-0',
    ];

    protected function getListeners()
    {
        return [
            'mapReady' => 'updateMap',
            'mapClicked' => 'updateMap',
            'levelTransitioned' => 'updateMap'
        ];
    }

    protected function getGeoJson(array $parentPaths = [])
    {
        $lqueryArray = "ARRAY[" . collect($parentPaths)->map(fn ($path) => "'{$path}.*{1}'")->join(', ') . "]::lquery[]";
        $whereClause = empty($parentPaths) ? "level = 0" : "path ?? $lqueryArray";
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
        //logger('getGeoJson()', ['sql' => $sql]);
        try {
            $result = DB::select($sql);
        } catch (Exception $exception) {
            logger('Query error in getGeoJson()', ['exception' => $exception->getMessage()]);
            return '';
        }
        return $result[0]->feature_collection;
    }

    public function getData(array $parentPaths = [])
    {
        return (new Population('households'))->getData();
    }

    final public function updateMap(array $parentPaths = [])
    {
        //logger('updateMap()', ['$parentPaths' => $parentPaths]);
        $this->geojson = $this->getGeoJson($parentPaths);
        $level = empty($parentPaths) ? 0 : str($parentPaths[0])->explode('.')->count();
        $this->emit('geojsonUpdated', json_decode($this->geojson), $level, $this->getData());
    }

    final public function mount()
    {
        $this->mapOptions = [
            'center' => config('chimera.area.map.center'),
            'zoom' => 6,
            'attributionControl' => false,
        ];
        $this->styles = [
            'default' => self::DEFAULT_STYLE
        ];
    }

    public function render()
    {
        return view('livewire.map');
    }
}
