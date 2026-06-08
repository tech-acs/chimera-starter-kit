<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Validation\MapIndicatorValidationRules;

#[Description('Create a new map indicator (colored map area) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/MapIndicators/{Name}.php.')]
class CreateMapIndicator extends Tool
{
    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        $validator = Validator::make($request->toArray(), MapIndicatorValidationRules::rules());

        if ($validator->fails()) {
            return Response::error('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $validated = $validator->validated();
        $attributes = new MapIndicatorAttributes(
            name: $validated['name'],
            title: $validated['title'],
            description: $validated['description'] ?? null,
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/map_indicators/default.stub')
        );

        $result = $createArtefactAction->execute(modelClass: MapIndicator::class, baseNamespace: '\MapIndicators', attributes: $attributes);

        if ($result->success) {
            return Response::text('Map indicator created successfully');
        }

        return Response::error("Failed to create map indicator. {$result->errorMessage}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase, including directory if any (e.g., "MyMapIndicator", "Region/CompletionMap")'),
            'title' => $schema->string()->description('Human-readable title'),
            'description' => $schema->string()->description('Human-readable description'),
            'data_source' => $schema->string()->description('Name of the data source this map indicator queries'),
        ];
    }
}
