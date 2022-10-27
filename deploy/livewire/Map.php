<?php

namespace App\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Map extends Component
{
    protected $listeners = [
        'map-ready' => 'prepareInitialMap',
        'map-clicked' => 'prepareClickedAreaSubMap'
    ];

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
    ];

    public string $geojson;
    public array $styles;
    public array $mapOptions;

    /*public function getGeoJsonV1(string $areaType, array $boundingBox)
    {
        list($x1, $y1, $x2, $y2) = $boundingBox;
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
                            'style', 'orange'
                        )
                    )
                )
            ) AS feature_collection
            FROM
                 (
                    SELECT name, code, area_type, geom
                    FROM
                         maps
                    WHERE
                        area_type = '{$areaType}' AND
                        geom::geometry && ST_MakeEnvelope($x1, $y1, $x2, $y2, 4326)
                 ) AS filtered_areas
        ";
        try {
            $result = DB::select($sql);
        } catch (Exception $exception) {
            return '';
        }
        return $result[0]->feature_collection;
    }

    private function areaTypeFromZoomLevel(int $zoomLevel) : string {
        return match($zoomLevel) {
            6, 7 => 'region',
            8, 9, 10, 11, 12 => 'constituency',
            13, 14, 15, 16, 17 => 'ea'
        };
    }*/

    private function getGeoJson(?string $path = null)
    {
        $whereClause = is_null($path) ? "level = 0" : "path <@ '{$path}'";
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
                SELECT name, code, geom, level, path
                FROM areas
                WHERE $whereClause
            ) AS filtered_areas
        ";
        try {
            $result = DB::select($sql);
        } catch (Exception $exception) {
            return '';
        }
        return $result[0]->feature_collection;
    }

    public function prepareInitialMap()
    {
        $this->geojson = $this->getGeoJson();
        $this->emit('updateMap', json_decode($this->geojson));
    }

    public function prepareClickedAreaSubMap(string $path)
    {
        $this->geojson = $this->getGeoJson($path);
        $this->emit('updateMap', json_decode($this->geojson));
    }

    public function mount()
    {
        $this->mapOptions = [
            'center' => config('chimera.area.map.center'),
            'zoom' => 5,
            'attributionControl' => false,
        ];
        //$this->geojson = $this->getGeoJson();
        $this->styles = [
            'default' => self::DEFAULT_STYLE
        ];
    }

    public function render()
    {
        return view('livewire.map');
    }
}
