<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        $records = collect([]);
        return view('developer.target.index', compact('records'));
    }

    public function create()
    {
        $levels = config('chimera.area.hierarchies', []);
        return view('developer.target.create', compact('levels'));
    }

    public function store(Request $request)
    {
        //
    }

    public function edit($id)
    {
        return view('developer.target.edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
