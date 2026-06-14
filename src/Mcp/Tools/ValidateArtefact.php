<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\DashboardComponentFactory;

#[Description('Validate a generated artefact by executing getData() and confirming it returns data. Call this after implementing getData() in a created artefact. Runs three checks: (1) data source connectivity, (2) artefact instantiation, (3) getData() execution with an empty filter path (national scope). Returns success status, row count, column names, and the first row as a sample. If this tool fails, report the error and stop — do not fall back to workarounds.')]
class ValidateArtefact extends Tool
{
    public function handle(Request $request): Response
    {
        $type = (string) $request->string('type', '');
        $name = (string) $request->string('name', '');

        if ($type === '') {
            return Response::error('type is required');
        }

        if ($name === '') {
            return Response::error('name is required');
        }

        $modelClass = match ($type) {
            'scorecard' => Scorecard::class,
            'gauge' => Gauge::class,
            'indicator' => Indicator::class,
            'map-indicator' => MapIndicator::class,
            'report' => Report::class,
            default => null,
        };

        if ($modelClass === null) {
            return Response::error("Invalid type '{$type}'. Must be one of: scorecard, gauge, indicator, map-indicator, report");
        }

        $model = $modelClass::where('name', $name)->first();

        if ($model === null) {
            return Response::error("{$type} with name '{$name}' not found");
        }

        try {
            DB::connection($model->data_source)->getPdo();
        } catch (\Exception $e) {
            return Response::error("Data source '{$model->data_source}' is not connectible: " . $e->getMessage());
        }

        $instance = match ($type) {
            'scorecard' => DashboardComponentFactory::makeScorecard($model),
            'gauge' => DashboardComponentFactory::makeGauge($model),
            'indicator' => DashboardComponentFactory::makeIndicator($model),
            'map-indicator' => DashboardComponentFactory::makeMapIndicator($model),
            'report' => DashboardComponentFactory::makeReport($model),
        };

        if ($instance === null) {
            return Response::error("Failed to instantiate {$type} '{$name}'. The class file may not exist or may have syntax errors.");
        }

        try {
            $data = $instance->getData('');
        } catch (\Exception $e) {
            return Response::error("getData() execution failed: " . $e->getMessage());
        }

        if ($data === null || $data->isEmpty()) {
            return Response::text("getData() executed successfully but returned no data.");
        }

        $firstRow = $data->first();
        $columns = array_keys((array) $firstRow);

        $result = [
            'success' => true,
            'rows_returned' => $data->count(),
            'columns' => $columns,
            'sample_data' => $firstRow,
        ];

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(['scorecard', 'gauge', 'indicator', 'map-indicator', 'report'])
                ->description('Type of artefact to validate'),
            'name' => $schema->string()
                ->description('Name of the artefact (e.g. "TotalFemalePopulation", "Households/BirthRate"). Matches the name used in the create-* tool call.'),
        ];
    }
}
