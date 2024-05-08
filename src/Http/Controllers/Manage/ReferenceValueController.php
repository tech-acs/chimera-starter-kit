<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\ReferenceValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class ReferenceValueController extends Controller
{
    public function index(Request $request)
    {
        view()->share('hierarchies', (new AreaTree())->hierarchies);
        $stats = ReferenceValue::selectRaw('COUNT(DISTINCT indicator) AS no_of_indicators, COUNT(*) AS total_values')->first();
        $summary = Str::replaceArray('?', [$stats->total_values, $stats->no_of_indicators], "? reference values across ? " . Str::plural('indicator', $stats->no_of_indicators));

        return (new SmartTableData(ReferenceValue::query(), $request))
            ->columns([
                SmartTableColumn::make('indicator')->sortable(),
                SmartTableColumn::make('path')->sortable()->setLabel('Area Path'),
                SmartTableColumn::make('level')->sortable()
                    ->setBladeTemplate('{{ ucfirst($hierarchies[$row->level] ?? $row->level) }}'),
                SmartTableColumn::make('value'),
            ])
            ->searchable(['indicator, path'])
            ->sortBy('indicator')
            ->view('chimera::developer.reference-value.index', compact('summary'));
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
