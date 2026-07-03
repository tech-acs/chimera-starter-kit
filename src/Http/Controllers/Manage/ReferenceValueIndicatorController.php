<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Uneca\Chimera\Models\ReferenceValueIndicator;

class ReferenceValueIndicatorController extends Controller
{
    public function index()
    {
        $referenceValueIndicators = ReferenceValueIndicator::withCount('referenceValues')->get();
        return view('chimera::developer.reference-value-indicator.index', compact('referenceValueIndicators'));
    }

    public function create()
    {
        return view('chimera::developer.reference-value-indicator.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'indicator' => ['required', 'string', Rule::unique(ReferenceValueIndicator::class)],
            'description' => 'required|string',
        ]);

        ReferenceValueIndicator::create($validated);

        return redirect()->route('developer.reference-value-indicator.index')
            ->withMessage('The indicator has been created');
    }

    public function edit(ReferenceValueIndicator $referenceValueIndicator)
    {
        return view('chimera::developer.reference-value-indicator.edit', compact('referenceValueIndicator'));
    }

    public function update(Request $request, ReferenceValueIndicator $referenceValueIndicator)
    {
        $validated = $request->validate(['description' => 'required|string']);
        $referenceValueIndicator->update($validated);

        return redirect()->route('developer.reference-value-indicator.index')
            ->withMessage('The item has been updated');
    }

    public function destroy(ReferenceValueIndicator $referenceValueIndicator)
    {
        if ($referenceValueIndicator->referenceValues()->count() > 0) {
            return redirect()->back()
                ->withErrors('Cannot delete an indicator that has reference values. Delete all values first.');
        }

        $referenceValueIndicator->delete();

        return redirect()->route('developer.reference-value-indicator.index')
            ->withMessage('The item has been deleted');
    }
}
