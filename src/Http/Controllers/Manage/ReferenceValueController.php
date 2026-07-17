<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Uneca\Chimera\Models\ReferenceValue;
use Uneca\Chimera\Models\ReferenceValueIndicator;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class ReferenceValueController extends Controller
{
    public function index(Request $request, ReferenceValueIndicator $referenceValueIndicator)
    {
        view()->share('hierarchies', (new AreaTree)->hierarchies);
        $totalValues = ReferenceValue::where('indicator', $referenceValueIndicator->indicator)->count();
        $summary = "$totalValues ".str('reference value')->plural($totalValues);

        return (new SmartTableData(
            ReferenceValue::where('indicator', $referenceValueIndicator->indicator),
            $request
        ))
            ->columns([
                SmartTableColumn::make('path')->sortable()->setLabel('Area Path'),
                SmartTableColumn::make('level')->sortable()
                    ->setBladeTemplate('{{ ucfirst($hierarchies[$row->level] ?? $row->level) }}'),
                SmartTableColumn::make('value'),
            ])
            ->editable('developer.reference-value-indicator.reference-value.edit', ['reference_value_indicator' => $referenceValueIndicator])
            ->searchable(['path'])
            ->sortBy('path')
            ->view('chimera::developer.reference-value.index', compact('summary', 'referenceValueIndicator'));
    }

    public function create(ReferenceValueIndicator $referenceValueIndicator)
    {
        return view('chimera::developer.reference-value.create', compact('referenceValueIndicator'));
    }

    public function edit(ReferenceValueIndicator $referenceValueIndicator, ReferenceValue $referenceValue)
    {
        return view('chimera::developer.reference-value.edit', compact('referenceValueIndicator', 'referenceValue'));
    }

    public function update(ReferenceValueIndicator $referenceValueIndicator, ReferenceValue $referenceValue, Request $request)
    {
        $referenceValue->update($request->only(['value']));

        return redirect()->route('developer.reference-value-indicator.reference-value.index', $referenceValueIndicator)
            ->withMessage('The reference value has been updated');
    }

    public function destroy(ReferenceValueIndicator $referenceValueIndicator)
    {
        ReferenceValue::where('indicator', $referenceValueIndicator->indicator)->delete();

        return redirect()->route('developer.reference-value-indicator.reference-value.index', $referenceValueIndicator)
            ->withMessage('All reference values for "'.$referenceValueIndicator->indicator.'" have been deleted');
    }
}
