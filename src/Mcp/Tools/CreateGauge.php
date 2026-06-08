<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Validation\GaugeValidationRules;

#[Description('Create a new gauge (visual threshold card) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/Livewire/{Name}.php.')]
class CreateGauge extends Tool
{
    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        $validator = Validator::make($request->toArray(), GaugeValidationRules::rules());

        if ($validator->fails()) {
            return Response::error('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $validated = $validator->validated();
        $gaugeAttributes = new GaugeAttributes(
            name: $validated['name'],
            title: $validated['title'],
            subtitle: $validated['subtitle'],
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/gauges/default.stub')
        );

        $result = $createArtefactAction->execute(modelClass: Gauge::class, baseNamespace: 'Livewire\Gauge', attributes: $gaugeAttributes);

        if ($result->success) {
            return Response::text('Gauge created successfully');
        }

        return Response::error("Failed to create gauge. {$result->errorMessage}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase, including directory if any (e.g., "EnumeratedHouseholds", "Households/BirthRate")'),
            'title' => $schema->string()->description('Human-readable title'),
            'subtitle' => $schema->string()->description('Human-readable subtitle'),
            'data_source' => $schema->string()->description('Name of the data source this gauge queries'),
        ];
    }
}
