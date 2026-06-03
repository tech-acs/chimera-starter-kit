<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateIndicatorAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\ResolvesStubPath;
use Uneca\Chimera\Models\Indicator;

#[Description('Create a new indicator (Plotly chart) artefact. Generates a Livewire component file from a stub and creates the database record. Optionally accepts Plotly traces (data) and layout JSON. Supported chart types: bar, line, scatter, pie, histogram, area, box, sunburst. The getData() method must prepare all data columns the chart\'s traces reference via meta.columnNames.')]
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

        app(CreateIndicatorAction::class)->execute($dto);

        $indicator = Indicator::where('name', $name)->firstOrFail();

        return Response::text("Indicator '{$title}' created. ID: {$indicator->id}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(
                description: 'Component name in CamelCase, optionally with subdirectory prefix (e.g., "Household/BirthRate")',
            ),
            'title' => $schema->string(
                description: 'Human-readable title for the indicator',
            ),
            'data_source' => $schema->string(
                description: 'Name of the data source (questionnaire) this indicator queries',
            ),
            'description' => $schema->string(
                description: 'Optional description of what this indicator shows',
            ),
            'type' => $schema->string(
                description: 'Stub variant (default, template, default-with-sample-code)',
            ),
            'data' => $schema->array(
                description: 'Optional Plotly traces array. Each trace is an object with type (bar|line|scatter|pie|histogram|area|box|sunburst), x, y, name, meta.columnNames mapping output columns to trace properties, etc.',
                items: $schema->object(),
            ),
            'layout' => $schema->object(
                description: 'Optional Plotly layout object (title, xaxis, yaxis, legend, margin, colorway, etc.)',
            ),
        ];
    }
}
