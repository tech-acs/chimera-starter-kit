<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\ReferenceValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Uneca\Chimera\Services\AreaTree;

class ReferenceValueController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $records = ReferenceValue::orderBy('level')->orderBy('indicator')->orderBy('path')
            ->when(! empty($search), function ($query) use ($search) {
                $query->whereRaw("indicator ilike '{$search}%'");
            })
            ->paginate(config('chimera.records_per_page'));
        $stats = ReferenceValue::selectRaw('COUNT(DISTINCT indicator) AS no_of_indicators, COUNT(*) AS total_values')->first();
        $summary = Str::replaceArray('?', [$stats->total_values, $stats->no_of_indicators], "? reference values across ? " . Str::plural('indicator', $stats->no_of_indicators));
        $hierarchies = (new AreaTree())->hierarchies;
        return view('chimera::developer.reference-value.index', compact('records', 'summary', 'hierarchies'));
    }

    public function create()
    {
        return view('chimera::developer.reference-value.create');
    }

    public function edit(ReferenceValue $referenceValue)
    {
        return view('chimera::developer.reference-value.edit', compact('referenceValue'));
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
