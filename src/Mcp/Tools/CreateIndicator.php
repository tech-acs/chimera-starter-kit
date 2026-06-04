<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateIndicatorAction;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\QueryException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\ResolvesStubPath;
use Uneca\Chimera\Models\Indicator;
use InvalidArgumentException;

#[Description('Create a new indicator (Plotly chart) artefact. Generates a Livewire component file from a stub and creates the database record. Optionally accepts Plotly traces (data) and layout JSON. Use chart_type to auto-generate sensible trace defaults. Supported: bar, line, scatter, pie, histogram, area, box, sunburst. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/Livewire/{Name}.php — the query must return columns matching your traces\' meta.columnNames.')]
class CreateIndicator extends Tool
{
    use ResolvesStubPath;

    public function handle(Request $request): Response
    {
        $name = $request->string('name');
        $title = $request->string('title');
        $dataSource = $request->string('data_source');
        $description = $request->string('description', '');
        $data = $request->array('data', []);
        $layout = $request->array('layout', []);
        $type = $request->string('type', 'default');
        $chartType = $request->string('chart_type', '');

        if ($chartType !== '' && ! in_array($chartType, ['bar', 'line', 'scatter', 'pie', 'histogram', 'area', 'box', 'sunburst'])) {
            return Response::error("Invalid chart_type '{$chartType}'. Supported: bar, line, scatter, pie, histogram, area, box, sunburst.");
        }

        if (empty($data) && $chartType !== '') {
            $defaults = $this->chartTypeDefaults($chartType);
            $data = $defaults['data'];
            $layout = array_merge($defaults['layout'], $layout);
        }

        $dto = new IndicatorAttributes(
            name: $name,
            title: $title,
            dataSource: $dataSource,
            type: $type,
            description: $description ?: null,
            data: $data,
            layout: $layout,
            stub: $this->resolveStubPath("indicators/{$type}.stub"),
        );

        try {
            app(CreateIndicatorAction::class)->execute($dto);
        } catch (Exception $e) {
            return Response::error("Failed to create indicator: {$e->getMessage()}");
        }

        try {
            $indicator = Indicator::where('name', $name)->firstOrFail();
        } catch (QueryException $e) {
            return Response::error('Failed to retrieve created indicator. The database table may not exist. Run the package migrations.');
        }

        return Response::text("Indicator '{$title}' created. ID: {$indicator->id}");
    }

    private function chartTypeDefaults(string $chartType): array
    {
        $colorway = ['#636efa', '#ef553b', '#00cc96', '#ab63fa', '#ffa15a', '#19d3f3', '#ff6692', '#b6e880', '#ff97ff', '#fecb52'];

        $defaults = match ($chartType) {
            'bar' => [
                'data' => [
                    ['type' => 'bar', 'x' => [], 'y' => [], 'name' => '', 'meta' => ['columnNames' => ['x' => ['x'], 'y' => ['y']]]],
                ],
                'layout' => ['barmode' => 'relative', 'xaxis' => ['title' => ''], 'yaxis' => ['title' => '']],
            ],
            'line' => [
                'data' => [
                    ['type' => 'scatter', 'mode' => 'lines+markers', 'x' => [], 'y' => [], 'name' => '', 'meta' => ['columnNames' => ['x' => ['x'], 'y' => ['y']]]],
                ],
                'layout' => ['xaxis' => ['title' => ''], 'yaxis' => ['title' => '']],
            ],
            'scatter' => [
                'data' => [
                    ['type' => 'scatter', 'mode' => 'markers', 'x' => [], 'y' => [], 'name' => '', 'meta' => ['columnNames' => ['x' => ['x'], 'y' => ['y']]]],
                ],
                'layout' => ['xaxis' => ['title' => ''], 'yaxis' => ['title' => '']],
            ],
            'pie' => [
                'data' => [
                    ['type' => 'pie', 'labels' => [], 'values' => [], 'meta' => ['columnNames' => ['labels' => ['labels'], 'values' => ['values']]]],
                ],
                'layout' => [],
            ],
            'histogram' => [
                'data' => [
                    ['type' => 'histogram', 'x' => [], 'meta' => ['columnNames' => ['x' => ['x']]]],
                ],
                'layout' => ['xaxis' => ['title' => ''], 'yaxis' => ['title' => '']],
            ],
            'area' => [
                'data' => [
                    ['type' => 'scatter', 'mode' => 'lines', 'fill' => 'tozeroy', 'x' => [], 'y' => [], 'name' => '', 'meta' => ['columnNames' => ['x' => ['x'], 'y' => ['y']]]],
                ],
                'layout' => ['xaxis' => ['title' => ''], 'yaxis' => ['title' => '']],
            ],
            'box' => [
                'data' => [
                    ['type' => 'box', 'y' => [], 'name' => '', 'meta' => ['columnNames' => ['y' => ['y']]]],
                ],
                'layout' => ['yaxis' => ['title' => '']],
            ],
            'sunburst' => [
                'data' => [
                    ['type' => 'sunburst', 'labels' => [], 'parents' => [], 'values' => [], 'meta' => ['columnNames' => ['labels' => ['labels'], 'parents' => ['parents'], 'values' => ['values']]]],
                ],
                'layout' => [],
            ],
        };

        $defaults['layout']['colorway'] ??= $colorway;
        $defaults['layout']['font'] ??= ['family' => 'Inter, system-ui, sans-serif'];
        $defaults['layout']['margin'] ??= ['t' => 40, 'b' => 40, 'l' => 40, 'r' => 20];

        return $defaults;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase, optionally with subdirectory prefix (e.g., "Household/BirthRate")'),
            'title' => $schema->string()->description('Human-readable title for the indicator'),
            'data_source' => $schema->string()->description('Name of the data source (questionnaire) this indicator queries'),
            'description' => $schema->string()->description('Optional description of what this indicator shows'),
            'type' => $schema->string()->description('Stub variant (default, template, default-with-sample-code)'),
            'chart_type' => $schema->string()->description('Optional chart type to auto-generate Plotly traces and layout. If omitted, you must provide data and layout explicitly. If the user didn\'t specify a type, recommend one based on the data and ask them to confirm. Supported: bar, line, scatter, pie, histogram, area, box, sunburst.'),
            'data' => $schema->array()->description('Optional Plotly traces array. Each trace is an object with type (bar|line|scatter|pie|histogram|area|box|sunburst), x, y, name, meta.columnNames mapping output columns to trace properties, etc. If chart_type is provided, these override the auto-generated defaults.')->items($schema->object()),
            'layout' => $schema->object()->description('Optional Plotly layout object (title, xaxis, yaxis, legend, margin, colorway, etc.). Merged over any auto-generated layout defaults.'),
        ];
    }
}
