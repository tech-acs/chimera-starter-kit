<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Uneca\Chimera\Http\Requests\MapRequest;
use Uneca\Chimera\Jobs\ImportShapefileJob;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Uneca\Chimera\Services\ShapefileImporter;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $areaCounts = Area::select('level', DB::raw('count(*) AS count'))->groupBy('level')->get()->keyBy('level');
        $hierarchies = (new AreaTree())->hierarchies;
        $summary = collect($hierarchies)->map(function ($levelName, $level) use ($areaCounts) {
            return ($areaCounts[$level]?->count ?? 0) . ' ' . str($levelName)->plural();
        })->join(', ', ' and ');
        view()->share('hierarchies', $hierarchies);

        return (new SmartTableData(Area::query(), $request))
            ->columns([
                SmartTableColumn::make('name')->sortable(),
                SmartTableColumn::make('code')->sortable(),
                SmartTableColumn::make('level')->sortable()
                    ->setBladeTemplate('{{ ucfirst($hierarchies[$row->level] ?? $row->level) }}'),
                SmartTableColumn::make('path'),
                SmartTableColumn::make('geom')->setLabel('Has Map')
                    ->setBladeTemplate('<x-chimera::yes-no value="{{ $row->geom }}" />'),
            ])
            ->editable('developer.area.edit')
            ->searchable(['name', 'code'])
            ->sortBy('level')
            ->downloadable()
            ->view('chimera::developer.area.index', compact('summary'));
    }

    public function create()
    {
        $levels = (new AreaTree)->hierarchies;
        return view('chimera::developer.area.create', ['levels' => array_map(fn ($level) => ucfirst($level), $levels)]);
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

        $importer = new ShapefileImporter();
        $sampleFeature = $importer->sample($filePath);

        // Check for empty shapefiles
        if (empty($sampleFeature)) {
            throw ValidationException::withMessages([
                'shapefile' => ['The shapefile does not contain any valid features.'],
            ]);
        }

        // Check that shapefile has 'name' and 'code' columns in the attribute table
        if (! (array_key_exists('name', $sampleFeature['attribs']) && array_key_exists('code', $sampleFeature['attribs']))) {
            throw ValidationException::withMessages([
                'shapefile' => ["The shapefile needs to have 'name' and 'code' attributes"],
            ]);
        }

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
