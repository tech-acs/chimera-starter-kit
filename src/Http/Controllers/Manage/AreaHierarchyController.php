<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Uneca\Chimera\Http\Requests\AreaHierarchyRequest;
use Uneca\Chimera\Models\AreaHierarchy;
use Illuminate\Http\Request;

class AreaHierarchyController extends Controller
{
    public function index()
    {
        $records = AreaHierarchy::orderBy('index')->get();
        return view('chimera::developer.area-hierarchy.index', compact('records'));
    }

    public function create()
    {
        return view('chimera::developer.area-hierarchy.create');
    }

    private function validateZoomRange($request)
    {
        $validator = Validator::make(
            [
                'zoom_start' => $request->integer('zoom_start'),
                'zoom_end' => $request->integer('zoom_end')
            ],
            [
                'zoom_start' => 'integer|lte:zoom_end|min:6',
                'zoom_end' => 'integer|gte:zoom_start|min:6',
            ]
        );
        if ($validator->fails()) {
            throw ValidationException::withMessages(['map_zoom_levels' => 'Map zoom levels must be a valid range']);
        }
    }

    public function store(AreaHierarchyRequest $request)
    {
        $this->validateZoomRange($request);
        $zoomLevels = range($request->integer('zoom_start'), $request->integer('zoom_end'));
        AreaHierarchy::create([
            'index' => AreaHierarchy::count(),
            'name' => $request->get('name'),
            'zero_pad_length' => $request->get('zero_pad_length'),
            'simplification_tolerance' => $request->get('simplification_tolerance'),
            'map_zoom_levels' => $zoomLevels,
        ]);
        return redirect()->route('developer.area-hierarchy.index')->withMessage('Area hierarchy created');
    }

    public function edit(AreaHierarchy $areaHierarchy)
    {
        $areaHierarchy->zoom_start = min($areaHierarchy->map_zoom_levels ?? [6]);
        $areaHierarchy->zoom_end = max($areaHierarchy->map_zoom_levels ?? [6]);
        return view('chimera::developer.area-hierarchy.edit', compact('areaHierarchy'));
    }

    public function update(AreaHierarchy $areaHierarchy, AreaHierarchyRequest $request)
    {
        $this->validateZoomRange($request);
        $zoomLevels = range($request->integer('zoom_start'), $request->integer('zoom_end'));
        $areaHierarchy->update($request->merge(['map_zoom_levels' => $zoomLevels])->only(['name', 'zero_pad_length', 'simplification_tolerance', 'map_zoom_levels']));
        return redirect()->route('developer.area-hierarchy.index')->withMessage('Area hierarchy updated');
    }

    public function destroy(AreaHierarchy $areaHierarchy)
    {
        $areaHierarchy->delete();
        return redirect()->route('developer.area-hierarchy.index')->withMessage('Area hierarchy deleted');
    }
}
