<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\ReferenceValueIndicator;

#[Description('List available reference value indicators with their descriptions. Reference values are precomputed comparison values (e.g. population counts, household counts) used to show diffs in scorecards/gauges or a reference/contrast line in indicator charts.

Reference values are optional — only include them if the user explicitly asks for a comparison or reference line. Each indicator has a `description` field you should examine to select the correct one for the artefact\'s subject.

Usage: pass the name as referenceValueToInclude in any lastlyArea*() method on BreakoutQueryBuilder. The resulting column is called reference_value. Use it in your getData() — for scorecards/gauges compute diff = value - reference_value, for indicators add a separate Plotly trace.')]
class GetReferenceValues extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $indicators = ReferenceValueIndicator::query()
            ->selectRaw('reference_value_indicators.indicator, description, COUNT(rv.id) AS total_values, ARRAY_AGG(DISTINCT rv.level ORDER BY rv.level) AS levels')
            ->leftJoin('reference_values AS rv', 'rv.indicator', '=', 'reference_value_indicators.indicator')
            ->groupBy('reference_value_indicators.indicator', 'description')
            ->orderBy('reference_value_indicators.indicator')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->indicator,
                'description' => $row->description,
                'total_values' => (int) $row->total_values,
                'levels' => $row->levels,
            ]);

        if ($indicators->isEmpty()) {
            return Response::text('No reference value indicator metadata found. Create indicator entries via the management UI before importing reference values.');
        }

        return Response::structured([
            'indicators' => $indicators->toArray(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
