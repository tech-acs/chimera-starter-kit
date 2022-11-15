<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\ReferenceValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferenceValueController extends Controller
{
    public function index()
    {
        $records = ReferenceValue::orderBy('level')->paginate(config('chimera.records_per_page'));
        $stats = ReferenceValue::selectRaw('COUNT(DISTINCT indicator) AS no_of_indicators, COUNT(*) AS total_values')->first();
        $summary = Str::replaceArray('?', [$stats->total_values, $stats->no_of_indicators], "? reference values across ? " . Str::plural('indicator', $stats->no_of_indicators));
        return view('developer.reference-value.index', compact('records', 'summary'));
    }

    public function create()
    {
        return view('developer.reference-value.create');
    }

    public function edit(ReferenceValue $referenceValue)
    {
        return view('developer.reference-value.edit', compact('referenceValue'));
    }

    public function update(ReferenceValue $referenceValue, Request $request)
    {
        $referenceValue->update($request->only(['value']));
        return redirect()->route('developer.reference-value.index')
            ->withMessage("The reference value has been updated");
    }

    public function destroy()
    {
        ReferenceValue::truncate();
        return redirect()->route('developer.reference-value.index')
            ->withMessage("The reference values table has been truncated");
    }
}
