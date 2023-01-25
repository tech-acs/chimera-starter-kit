<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\MapRequest;
use Uneca\Chimera\Jobs\ImportShapefileJob;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Traits\Geospatial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AreaController extends Controller
{
    use Geospatial;

    public function index(Request $request)
    {
        $search = $request->get('search');
        $locale = app()->getLocale();
        $records = Area::orderBy('level')->orderBy('name')
            ->when(! empty($search), function ($query) use ($search, $locale) {
                $query->whereRaw("name->>'{$locale}' ilike '{$search}%'")
                    ->orWhere('code', $search);
            })
            ->paginate(config('chimera.records_per_page'));
        $areaCounts = Area::select('level', DB::raw('count(*) AS count'))->groupBy('level')->get()->keyBy('level');
        $hierarchies = (new AreaTree())->hierarchies;
        $summary = collect($hierarchies)->map(function ($levelName, $level) use ($areaCounts) {
            return ($areaCounts[$level]?->count ?? 0) . ' ' . str($levelName)->plural();
        })->join(', ', ' and ');
        return view('chimera::developer.area.index', compact('records', 'summary', 'hierarchies'));
    }

    public function create()
    {
        $levels = (new AreaTree)->hierarchies; //config('chimera.area.hierarchies', []);
        return view('chimera::developer.area.create', ['levels' => array_map(fn ($level) => ucfirst($level), $levels)]);
    }

    private function validateShapefile(array $features)
    {
        // Check for empty shapefiles?
        if (empty($features)) {
            throw ValidationException::withMessages([
                'shapefile' => ['The shapefile does not contain any valid features.'],
            ]);
        }

        // Check that shapefile has 'name' and 'code' columns in the attribute table
        $firstFeatureAttributes = $features[0]['attribs'];
        if (! (array_key_exists('name', $firstFeatureAttributes) && array_key_exists('code', $firstFeatureAttributes))) {
            throw ValidationException::withMessages([
                'shapefile' => ["The shapefile needs to have 'name' and 'code' among its attributes"],
            ]);
        }
    }

    public function store(MapRequest $request)
    {
        $level = $request->integer('level');
        $files = $request->file('shapefile');
        $filename = Str::random(40);
        foreach ($files as $file) {
            $filenameWithExt = collect([$filename, $file->getClientOriginalExtension()])->join('.');
            $file->storeAs('/shapefiles', $filenameWithExt, 'imports');
        }
        $shpFile = collect([$filename, 'shp'])->join('.');
        $filePath = Storage::disk('imports')->path('shapefiles/' . $shpFile);
        /*$importer = new ShapefileImporter();
        $features = $importer->import($filePath);
        $this->validateShapefile($features);*/

        ImportShapefileJob::dispatch($filePath, $level, auth()->user(), app()->getLocale());

        return redirect()->route('developer.area.index')
            ->withMessage("Importing is in progress. You will be notified when it is complete.");
    }

    public function edit(Area $area)
    {
        return view('chimera::developer.area.edit', compact('area'));
    }

    public function update(Area $area, Request $request)
    {
        $area->update($request->only(['name', 'code']));
        return redirect()->route('developer.area.index')
            ->withMessage("The area has been updated");
    }

    public function destroy()
    {
        Area::truncate();
        return redirect()->route('developer.area.index')
            ->withMessage("The areas table has been truncated");
    }
}
