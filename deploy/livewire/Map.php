<?php

namespace App\Http\Livewire;

use App\Services\QueryFragmentFactory;
use App\Services\Traits\Cachable;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

abstract class Map extends Component
{
    use Cachable;

    public string $graphDiv;
    public string $mode;
    public string $data;
    public string $layout;
    public string $config;
    public string $connection;
    public string $help;

    public function getGeoJson(array $filter = [])
    {
        list($areaType, $parentCodeCondition) = QueryFragmentFactory::make($this->connection)->getMapQueryFragements($filter);
        $sql = "
            SELECT json_build_object(
                'type', 'FeatureCollection',
                'crs',  json_build_object(
                    'type',      'name', 
                    'properties', json_build_object(
                        'name', 'EPSG:4326'  
                    )
                ), 
                'features', json_agg(
                    json_build_object(
                        'type',       'Feature',
                        'geometry',   ST_AsGeoJSON(ST_GeomFromWKB(geom))::json,
                        'properties', json_build_object(
                            'code', code,
                            'name', name
                        )
                    )
                )
            ) AS feature_collection
            FROM maps
            WHERE area_type = '{$areaType}' AND {$parentCodeCondition}
        ";

        try {
            $result = DB::select($sql);
        } catch (Exception $exception) {
            return '';
        }
        return $result[0]->feature_collection;
    }

    protected function setLayout(array $filter = [])
    {
        if (config('chimera.cache.enabled')) {
            $this->layout = Cache::tags(['geojson'])
                ->remember('geojson' . implode('-', $filter), config('chimera.cache.ttl'), function () use ($filter) {
                    return $this->getGeoJson($filter);
                });
        } else {
            $this->layout = $this->getGeoJson($filter);
        }
    }

    public function mount()
    {
        $filtersToApply = array_merge(
            auth()->user()->areaFilter($this->connection),
            session()->get($this->connection, [])
        );
        $this->setData($filtersToApply);
        $this->setLayout($filtersToApply);
    }

    public function render()
    {
        return view('livewire.map');
    }
}
