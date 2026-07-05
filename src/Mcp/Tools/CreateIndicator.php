<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Actions\Maker\CreateArtefactAction;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Traits\PlotlyDefaults;
use Uneca\Chimera\Validation\IndicatorValidationRules;

#[Description('Create a new indicator (Plotly chart) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call get-data-sources first, parse the dictionary with read-dictionary, read examples via get-artefact-examples. After creation: (1) implement getData() in the generated file using BreakoutQueryBuilder, (2) call guide-breakout-query-builder if you need API reference, (3) call validate-artefact to confirm it works, (4) call edit-chart to configure Plotly traces, (5) call edit-indicator to populate the help field with explanatory text (markdown) documenting which dictionary records/items the indicator queries (from read-dictionary) and what calculations (SQL aggregations, filters, crosstabs) it performs. If this tool fails, report the error and stop — do not fall back to workarounds.')]
class CreateIndicator extends Tool
{
    use PlotlyDefaults;
    use RequiresInitializedMcp;

    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

        $validator = Validator::make($request->toArray(), IndicatorValidationRules::rules());

        if ($validator->fails()) {
            return Response::error('Validation failed: '.implode(', ', $validator->errors()->all()));
        }

        $validated = $validator->validated();

        $dataSource = DataSource::where('name', $validated['data_source'])->first();

        if (is_null($dataSource)) {
            $available = DataSource::pluck('name')->implode(', ');

            return Response::error("Data source '{$validated['data_source']}' not found. Available: {$available}");
        }

        $validated['name'] = preg_replace('/\s+/', '', $dataSource->title).'/'.$validated['name'];

        $layoutInput = $validated['layout'] ?? [];
        if ($layoutInput === []) {
            $layoutInput = self::DEFAULT_LAYOUT;
        }

        $attributes = new IndicatorAttributes(
            name: $validated['name'],
            title: $validated['title'],
            dataSource: $validated['data_source'],
            type: 'default',
            description: $validated['description'] ?? null,
            data: $validated['data'] ?? [],
            layout: (array) $layoutInput,
            stub: resource_path('stubs/indicators/default.stub'),
        );

        $result = $createArtefactAction->execute(modelClass: Indicator::class, baseNamespace: '\Livewire\Indicator', attributes: $attributes);

        if ($result->success) {
            return Response::text("Indicator '{$result->artefact->name}' created successfully at {$result->filePath}. Use this full name (including the data source prefix) for all subsequent tools (edit-chart, validate-artefact, edit-indicator). Next steps: (1) implement getData() in the generated file using BreakoutQueryBuilder, (2) call guide-breakout-query-builder for API reference, (3) call validate-artefact to test it, (4) call edit-chart to configure traces.");
        }

        return Response::error("Failed to create indicator. {$result->errorMessage}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase without the data source prefix (e.g., "BirthRate", "PopulationPyramid"). The data source title is automatically prepended as a directory (e.g., "Households/BirthRate"). Do NOT include the data source folder yourself.'),
            'title' => $schema->string()->description('Human-readable title'),
            'description' => $schema->string()->description('Human-readable description (max ~100 chars before truncation in chart-card)'),
            'data_source' => $schema->string()->description('Name of the data source this indicator queries (use the `name` field from get-data-sources, e.g. "households")'),
            'data' => $schema->array()->description('Optional array of Plotly trace objects. If omitted, an empty array is used — configure the traces afterwards via edit-chart.')->nullable(),
            'layout' => $schema->object()->description('Optional Plotly layout object. If omitted, a sensible default layout is used. You may override specific fields (e.g. title, xaxis, yaxis, margin).')->nullable(),
        ];
    }
}
