<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatRequest;
use App\Models\Stat;

class StatController extends Controller
{
    public function index()
    {
        $records = Stat::orderBy('title')->get();
        return view('stat.index', compact('records'));
    }

    public function edit(Stat $stat)
    {
        return view('stat.edit', compact('stat'));
    }

    public function update(Stat $stat, StatRequest $request)
    {
        $stat->update($request->only(['title', 'description', 'published']));
        return redirect()->route('stat.index')->withMessage('Record updated');
    }
}
