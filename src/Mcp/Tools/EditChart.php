<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\DashboardComponentFactory;

#[Description('Save the Plotly chart design (traces and layout) for an existing indicator. Call this AFTER implementing getData() — the tool verifies getData() returns data and validates that trace meta.columnNames match actual query result columns, then delegates the save to EditIndicator. Use your Plotly knowledge to craft the trace objects with type, meta.columnNames (matching your SQL aliases), name, hovertemplate, etc. The layout is optional.')]
class EditChart extends Tool
{
    public function handle(Request $request, EditIndicator $editIndicator): Response
    {
        $name = $request->get('name');
        if (empty($name)) {
            return Response::error('The "name" parameter is required');
        }

        $indicator = Indicator::withoutEagerLoads()->where('name', $name)->first();
        if (! $indicator) {
            return Response::error("Indicator '{$name}' not found");
        }

        $data = $request->get('data');
        if (empty($data) || ! is_array($data)) {
            return Response::error('The "data" parameter must be a non-empty array of Plotly trace objects');
        }

        $instance = DashboardComponentFactory::makeIndicator($indicator);
        if (is_null($instance)) {
            return Response::error("Failed to instantiate the indicator class. Ensure getData() is implemented and the file is valid.");
        }

        try {
            $queryData = $instance->getData('');
        } catch (\Throwable $e) {
            return Response::error("getData() threw an exception: {$e->getMessage()}. Fix getData() before designing the chart.");
        }

        if ($queryData->isEmpty()) {
            return Response::error('getData() returned no rows. The chart cannot be designed without data. Fix getData() first.');
        }

        $sampleRow = $queryData->first();
        $availableColumns = array_keys(get_object_vars($sampleRow));

        foreach ($data as $index => $trace) {
            $columnNames = Arr::get($trace, 'meta.columnNames');
            if (empty($columnNames)) {
                return Response::error("Trace at index {$index} is missing 'meta.columnNames'");
            }

            foreach ($columnNames as $key => $columnName) {
                $columns = is_array($columnName) ? $columnName : [$columnName];
                foreach ($columns as $col) {
                    if (! in_array($col, $availableColumns)) {
                        return Response::error(
                            "Trace at index {$index}: meta.columnNames.{$key} references '{$col}' "
                            . "but getData() returned columns: " . implode(', ', $availableColumns)
                        );
                    }
                }
            }
        }

        $editParams = [
            'name' => $name,
            'data' => $data,
        ];

        if ($request->has('layout')) {
            $editParams['layout'] = $request->get('layout');
        }

        $editRequest = new Request($editParams);

        $response = $editIndicator->handle($editRequest);

        $summary = count($data) . ' trace(s) configured';
        $columnsUsed = collect($data)
            ->flatMap(fn ($t) => Arr::flatten(Arr::get($t, 'meta.columnNames', [])))
            ->unique()
            ->values();

        return Response::text("Chart designed successfully. {$summary}. Columns matched: " . $columnsUsed->implode(', '));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Name of the indicator to design the chart for'),
            'data' => $schema->array()->description(
                'Array of Plotly trace objects. Each trace requires: '
                . '"type" (e.g. "bar", "scatter", "pie"), '
                . '"meta.columnNames" mapping trace properties (x, y, labels, values) to SQL aliases from getData(), '
                . '"name" (display label), '
                . 'and optionally "hovertemplate", "marker", etc. '
                . 'Example: [{"type":"bar","meta":{"columnNames":{"x":"area_name","y":["total"]}},"name":"Total","hovertemplate":"%{y}"}]'
            ),
            'layout' => $schema->object()->nullable()->description(
                'Optional Plotly layout object. Overrides the default layout. '
                . 'Common fields: title, xaxis, yaxis, showlegend, margin, etc. '
                . 'If omitted, a sensible default is used.'
            ),
        ];
    }
}
