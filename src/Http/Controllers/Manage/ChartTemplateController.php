<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Uneca\Chimera\Models\ChartTemplate;

class ChartTemplateController extends Controller
{
    public function index()
    {
        return ChartTemplate::all();
    }

    public function store(Request $request)
    {
        logger('received', ['received stuff' => $request->only(['name', 'category', 'description', 'data', 'layout'])]);
        ChartTemplate::create($request->only(['name', 'category', 'description', 'data', 'layout']));
        return response('Saved');
    }
}
