<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\AreaHierarchy;
use Illuminate\Http\Request;

class AreaHierarchyController extends Controller
{
    public function index()
    {
        $records = AreaHierarchy::orderBy('index')->get();
        return view('developer.area-hierarchy.index', compact('records'));
    }

    public function create()
    {
        return view('developer.area-hierarchy.create');
    }

    public function store(Request $request)
    {
        AreaHierarchy::create([
            'index' => AreaHierarchy::count(),
            'name' => $request->get('name')
        ]);
        return redirect()->route('developer.area-hierarchy.index')->withMessage('Area hierarchy created');
    }

    public function edit(AreaHierarchy $areaHierarchy)
    {
        return view('developer.area-hierarchy.edit', compact('areaHierarchy'));
    }

    public function update(AreaHierarchy $areaHierarchy, Request $request)
    {
        $areaHierarchy->update($request->only(['name']));
        return redirect()->route('developer.area-hierarchy.index')->withMessage('Area hierarchy updated');
    }

    public function destroy(AreaHierarchy $areaHierarchy)
    {
        $areaHierarchy->delete();
        return redirect()->route('developer.area-hierarchy.index')->withMessage('Page deleted');
    }
}
