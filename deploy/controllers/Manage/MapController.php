<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\MapRequest;
use App\Models\Area;
use App\Services\AreaTree;
use App\Services\ShapefileImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MapController extends Controller
{
    public function index()
    {
        $records = Area::paginate(config('chimera.records_per_page'));
        return view('developer.map.index', compact('records'));
    }

    public function create()
    {
        $levels = config('chimera.area.hierarchies', []);
        return view('developer.map.create', compact('levels'));
    }

    private static function findContainingGeometry($level, $geom)
    {
        return Area::ofLevel($level)
            ->whereRaw("ST_Area(ST_Intersection(geom::geometry, $geom)) > 0.70 * ST_Area($geom)")
            ->first();
    }

    private function makePath($ancestor, $code)
    {
        return is_null($ancestor) ? $code : $ancestor->path . '.' . $code;
    }

    private function augumentData(array $features, int $level)
    {
        return array_map(function ($feature) use ($level) {
            if ($level > 0) {
                $ancestor = self::findContainingGeometry((new AreaTree)->upperLevel($level), $feature['geom']);
                $feature['path'] = empty($ancestor) ? null : $this->makePath($ancestor, $feature['attribs']['code']);
            } else {
                $feature['path'] = $this->makePath(null, $feature['attribs']['code']);
            }
            return $feature;
        }, $features);
    }

    public function store(MapRequest $request)
    {
        $level = $request->integer('level', null);
        $files = $request->file('shapefile');
        $filename = Str::random(40);
        foreach ($files as $file) {
            $filenameWithExt = collect([$filename, $file->getClientOriginalExtension()])->join('.');
            $file->storeAs('/', $filenameWithExt, 'shapefiles');
        }
        $shpFile = collect([$filename, 'shp'])->join('.');
        $importer = new ShapefileImporter();
        $features = $importer->import(Storage::disk('shapefiles')->path($shpFile));

        if (empty($features)) {
            throw ValidationException::withMessages([
                'shapefile' => ['The shapefile does not contain any valid features'],
            ]);
        }
        $firstFeatureAttributes = $features[0]['attribs'];
        if (! (array_key_exists('name', $firstFeatureAttributes) && array_key_exists('code', $firstFeatureAttributes))) {
            throw ValidationException::withMessages([
                'shapefile' => ["The shapefile needs to have 'name' and 'code' among its attributes"],
            ]);
        }

        $augmentedFeatures = $this->augumentData($features, $level);

        $featuresMissingCode = array_filter($augmentedFeatures, fn ($feature) => empty($feature['attribs']['code']));
        if (! empty($featuresMissingCode)) {
            throw ValidationException::withMessages([
                'shapefile' => [count($featuresMissingCode) . " area(s) with no value for 'code' attribute found. Required for all."],
            ]);
        }

        // Validate that code is a sequence of alphanumeric characters and underscores (A-Za-z0-9_)
        // Must be less than 256 characters long.

        $orphanFeatures = array_filter($augmentedFeatures, fn ($feature) => empty($feature['path']));
        if (! empty($orphanFeatures)) {
            $orphans = collect($orphanFeatures)->pluck('attribs.code')->join(', ', ' and ');
            throw ValidationException::withMessages([
                'shapefile' => [count($orphanFeatures) . " orphan area(s) found [code: $orphans]. All areas require a containing parent area."],
            ]);
        }
        $results = [];
        foreach ($augmentedFeatures as $feature) {
            $results[] = Area::create([
                'name' => $feature['attribs']['name'],
                'code' => $feature['attribs']['code'],
                'level' => $level,
                'geom' => $feature['geom'],
                'path' => $feature['path'],
            ]);
        }
        $insertedCount = collect($results)->filter()->count();
        return redirect()->route('developer.area.index')
            ->withMessage("$insertedCount areas have been imported.");
    }

    public function edit(Area $area)
    {
        return view('developer.map.edit', compact('area'));
    }

    public function update(Area $area, Request $request)
    {
        $area->update($request->only(['name', 'code']));
        return redirect()->route('developer.area.index')
            ->withMessage("The area has been updated");
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return redirect()->route('developer.area.index')
            ->withMessage("The area has been deleted");
    }
}
