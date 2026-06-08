<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Validation\IndicatorValidationRules;

#[Description('Create a new indicator (Plotly chart) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/Livewire/{Name}.php.')]
class CreateIndicator extends Tool
{
    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        $validator = Validator::make($request->toArray(), IndicatorValidationRules::rules());

        if ($validator->fails()) {
            return Response::error('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $validated = $validator->validated();
        $chartType = $validated['chart_type'] ?? 'default';
        $stub = resource_path("stubs/indicators/{$chartType}.stub");

        $attributes = new IndicatorAttributes(
            name: $validated['name'],
            title: $validated['title'],
            dataSource: $validated['data_source'],
            type: $chartType,
            description: $validated['description'] ?? null,
            data: $validated['data'] ?? [],
            layout: $validated['layout'] ?? [
                'showlegend' => true,
                'legend' => ['orientation' => 'h', 'x' => 0, 'y' => 1.12],
                'xaxis' => ['type' => 'category', 'tickmode' => 'auto', 'automargin' => true],
                'margin' => ['l' => 60, 'r' => 30, 't' => 15, 'b' => 40],
                'dragmode' => 'pan',
            ],
            stub: $stub,
        );

        $result = $createArtefactAction->execute(modelClass: Indicator::class, baseNamespace: '\Livewire', attributes: $attributes);

        if ($result->success) {
            return Response::text('Indicator created successfully');
        }

        return Response::error("Failed to create indicator. {$result->errorMessage}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase, including directory if any (e.g., "HouseholdsEnumeratedByDay", "Region/PopulationPyramid")'),
            'title' => $schema->string()->description('Human-readable title'),
            'description' => $schema->string()->description('Human-readable description'),
            'data_source' => $schema->string()->description('Name of the data source this indicator queries'),
            'chart_type' => $schema->string()->default('default')->description('Chart type (default, bar, line, pie, etc.). Defaults to "default" which generates an empty stub. If a specific chart type (bar, line, pie) is given, the stub will include sample code for that chart type.'),
            'data' => $schema->string()->description('Optional JSON array of Plotly trace objects. If omitted, an empty array is used.'),
            'layout' => $schema->string()->description('Optional JSON object for Plotly layout. If omitted, a sensible default layout is used.'),
        ];
    }
}
