<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\GetDataResult;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\Indicator;

#[Description('Save the Plotly chart design (traces and layout) for an existing indicator. Call this AFTER implementing getData() — the tool verifies getData() returns data and validates that trace meta.columnNames match actual query result columns, then delegates the save to EditIndicator. Use your Plotly knowledge to craft the trace objects with type, meta.columnNames (matching your SQL aliases), name, hovertemplate, etc. The layout is optional.')]
class EditChart extends Tool
{
    use RequiresInitializedMcp;

    public function handle(Request $request, EditIndicator $editIndicator): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

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

        $result = $this->runFreshGetData('indicator', $name);

        if (! $result->success) {
            return Response::error($result->error ?? 'Failed to execute getData().');
        }

        if ($result->rowsReturned === 0) {
            return Response::error('getData() returned no rows. The chart cannot be designed without data. Fix getData() first.');
        }

        $availableColumns = $result->columns;

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
                            .'but getData() returned columns: '.implode(', ', $availableColumns)
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

        if ($response->isError()) {
            return $response;
        }

        $summary = count($data).' trace(s) configured';
        $columnsUsed = collect($data)
            ->flatMap(fn ($t) => Arr::flatten(Arr::get($t, 'meta.columnNames', [])))
            ->unique()
            ->values();

        return Response::text("Chart designed successfully. {$summary}. Columns matched: ".$columnsUsed->implode(', '));
    }

    private function runFreshGetData(string $type, string $name): GetDataResult
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $artisan = defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan';
        $cmd = sprintf(
            'php %s chimera:run-artefact-getdata %s %s 2>&1',
            $artisan,
            escapeshellarg($type),
            escapeshellarg($name),
        );

        $process = proc_open($cmd, $descriptorSpec, $pipes, base_path());

        if (! is_resource($process)) {
            return new GetDataResult(error: 'Failed to launch subprocess. shell_exec may be disabled.');
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0 || empty($output)) {
            return new GetDataResult(error: $stderr ?: 'Subprocess exited with code '.$exitCode);
        }

        $parsed = json_decode($output);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new GetDataResult(error: 'Failed to parse subprocess output: '.json_last_error_msg());
        }

        if (! ($parsed->success ?? false)) {
            return new GetDataResult(error: $parsed->error ?? 'Unknown error from subprocess');
        }

        return new GetDataResult(
            success: true,
            rowsReturned: $parsed->rows_returned ?? 0,
            columns: $parsed->columns ?? [],
            sampleData: $parsed->sample_data ?? null,
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Name of the indicator to design the chart for'),
            'data' => $schema->array()->description(
                'Array of Plotly trace objects. Each trace requires: '
                .'"type" (e.g. "bar", "scatter", "pie"), '
                .'"meta.columnNames" mapping trace properties (e.g. x, y, labels, values, text) to SQL aliases from getData(), '
                .'"name" (display label), '
                .'and optionally "hovertemplate", "marker", etc. '
                .'Example: [{"type":"bar","meta":{"columnNames":{"x":"area_name","y":["total"]}},"name":"Total","hovertemplate":"%{y}"}]'
            ),
            'layout' => $schema->object()->nullable()->description(
                'Optional Plotly layout object. Overrides the default layout. '
                .'Common fields: title, xaxis, yaxis, showlegend, margin, etc. '
                .'If omitted, a sensible default is used.'
            ),
        ];
    }
}
