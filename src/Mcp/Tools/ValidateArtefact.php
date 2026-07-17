<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\GetDataResult;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Models\Scorecard;

#[Description('Validate a generated artefact by executing getData() and confirming it returns data. Call this after implementing getData() in a created artefact. Runs three checks: (1) data source connectivity, (2) artefact instantiation, (3) getData() execution with an empty filter path (national scope). Returns success status, row count, column names, and the first row as a sample. If this tool fails, report the error and stop — do not fall back to workarounds.')]
class ValidateArtefact extends Tool
{
    use RequiresInitializedMcp;

    public function handle(Request $request): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

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
            return Response::error("Data source '{$model->data_source}' is not connectible: ".$e->getMessage());
        }

        $result = $this->runFreshGetData($type, $name);

        if (! $result->success) {
            return Response::error($result->error ?? 'An unknown error occurred while executing getData().');
        }

        if ($result->rowsReturned === 0) {
            return Response::text('getData() executed successfully but returned no data.');
        }

        $output = [
            'success' => true,
            'rows_returned' => $result->rowsReturned,
            'columns' => $result->columns,
            'sample_data' => $result->sampleData,
        ];

        return Response::text(json_encode($output, JSON_PRETTY_PRINT));
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
            'type' => $schema->string()->enum(['scorecard', 'gauge', 'indicator', 'map-indicator', 'report'])
                ->description('Type of artefact to validate'),
            'name' => $schema->string()
                ->description('FULL artefact name including data source prefix directory, as returned by the create-* tool (e.g. "KenyaCensus/TotalFemalePopulation", "Households/BirthRate"). NOT the bare name you passed as input to create-*.'),
        ];
    }
}
