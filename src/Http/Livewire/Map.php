<?php

namespace Uneca\Chimera\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Uneca\Chimera\MapIndicator\MapIndicatorBaseClass;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\MapIndicator;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\DashboardComponentFactory;
use Uneca\Chimera\Services\MapIndicatorCaching;

class Map extends Component
{
    public array $leafletMapOptions;
    public array $indicators = [];
    public string $currentIndicator;
    public array $previouslySentPaths = [];
    public array $simplification;
    public array $allStyles;
    public array $levels;

    protected function getListeners()
    {
        return [
            'mapReady' => 'setIndicatorAndUpdateMap',
            'mapClicked' => 'updateMap',
            'indicatorSelected' => 'setIndicatorAndUpdateMap',
        ];
    }

    protected function getGeoJson(string $parentPath, int $level)
    {
        $whereClause = empty($parentPath) ? "level = 0" : "path ~ '$parentPath.*{1}'";
        $simplificationTolerance = $this->simplification[$level];
        $sql = "
            SELECT
                CASE
                    WHEN COUNT(filtered_areas.*) = 0 THEN NULL
                    ELSE json_build_object(
                        'type', 'FeatureCollection',
                        'features', json_agg(
                            json_build_object(
                                'type',       'Feature',
                                'geometry',   ST_AsGeoJSON(filtered_areas.geom)::json,
                                'properties', json_build_object(
                                    'code', code,
                                    'name', name,
                                    'level', level,
                                    'path', path,
                                    'style', 'default'
                                )
                            )
                        )
                    )
                END
            AS feature_collection
            FROM
            (
                SELECT name, code, level, path, ST_SimplifyPreserveTopology(ST_GeomFromWKB(geom), $simplificationTolerance) AS geom
                FROM areas
                WHERE $whereClause AND geom IS NOT NULL
            ) AS filtered_areas
        ";
        try {
            $result = DB::select($sql);
        } catch (Exception $exception) {
            logger('Query error in getGeoJson()', ['exception' => $exception->getMessage()]);
            return '';
        }
        return $result[0]?->feature_collection ?? '';
    }

    private function getShapedData($mapIndicator, array $filter): Collection
    {
        $data = $mapIndicator->getData($filter) ?? collect([]);
        return $data->map(function ($row) use ($mapIndicator) {
            $row->value = $row->{$mapIndicator->valueField};
            $row->display_value = $row->{$mapIndicator->displayValueField} ?? null;
            $row->info = $row->{$mapIndicator->infoTextField} ?? null;
            $row->style = $mapIndicator->assignStyle($row->value);
            return $row;
        });
    }

    public function setCurrentIndicator(string $mapIndicator)
    {
        $this->currentIndicator = $mapIndicator;
        $currentIndicator = new $this->currentIndicator;
        $this->emit(
            'indicatorSwitched',
            $currentIndicator::SELECTED_COLOR_CHART,
            $currentIndicator->getLegend()
        );
    }

    final public function updateMap(string $path = '')
    {
        $nextLevel = empty($path) ? 0 : AreaTree::levelFromPath($path) + 1;
        $geojson = json_decode($this->getGeoJson($path, $nextLevel));
        if (! empty($geojson)) {
            $filtered = collect($geojson->features)->filter(fn ($feature) => ! in_array($feature->properties->path, $this->previouslySentPaths));
            $filteredPaths = $filtered->map(fn ($feature) => $feature->properties->path)->all();
            $this->previouslySentPaths = array_merge($this->previouslySentPaths, $filteredPaths);
            $geojson->features = $filtered->values()->all();
            $currentIndicator = new $this->currentIndicator;
            $this->emit('backendResponse', $geojson, $nextLevel, $currentIndicator->getMappableData($currentIndicator->getDataAndCacheIt($path), $path));
        } else {
            $this->emit('backendResponse', null, $nextLevel, []);
        }
    }

    public function setIndicatorAndUpdateMap(?string $mapIndicator = null)
    {
        if ((! empty($this->indicators)) && is_null($mapIndicator)) {
            $mapIndicator = array_key_first($this->indicators);
        }
        if (! empty($mapIndicator)) {
            $this->setCurrentIndicator($mapIndicator);
        }
        $this->updateMap('');
    }

    final public function mount()
    {
        $this->leafletMapOptions = [
            'center' => config('chimera.area.map.center'),
            'zoom' => 6,
            'minZoom' => 6,
            'zoomControl' => false,
            'attributionControl' => false,
            'preferCanvas' => true,
            'locale' => app()->getLocale(),
        ];
        $allStyles['default'] = MapIndicatorBaseClass::DEFAULT_STYLE;
        $this->indicators = MapIndicator::published()
            ->orderBy('rank')
            ->get()
            ->filter(function ($mapIndicator) {
                return Gate::allows($mapIndicator->permission_name);
            })
            ->mapWithKeys(function ($mapIndicator) use (&$allStyles) {
                $implementation = DashboardComponentFactory::makeMapIndicator($mapIndicator);
                $allStyles[$implementation::SELECTED_COLOR_CHART] = $implementation->getStyles();
                return [$mapIndicator->fully_qualified_classname => $mapIndicator->title];
            })
            ->all();
        $areaHierarchies = AreaHierarchy::orderBy('index')->get();
        $this->simplification = $areaHierarchies->pluck('simplification_tolerance')->all();
        $this->allStyles = $allStyles;
        $this->levels = array_map(fn ($levelName) => ucfirst($levelName), (new AreaTree())->hierarchies);

        /*if (! empty($this->indicators)) {
            $this->setCurrentIndicator(array_key_first($this->indicators));
        }*/
    }

    public function render()
    {
        return view('chimera::livewire.map');
    }
}
