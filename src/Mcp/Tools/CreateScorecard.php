<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Validation\ScorecardValidationRules;

#[Description('Create a new scorecard (numeric summary card) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/Livewire/{Name}.php.')]
class CreateScorecard extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        $validator = Validator::make($request->toArray(), ScorecardValidationRules::rules());

        if ($validator->fails()) {
            return Response::error('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $validated = $validator->validated();
        $scorecardAttributes = new ScorecardAttributes(
            name: $validated['name'],
            title: $validated['title'],
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/scorecards/default.stub')
        );

        $result = $createArtefactAction->execute(modelClass: Scorecard::class, baseNamespace: 'Livewire\Scorecard', attributes: $scorecardAttributes);

        if ($result->success) {
            return Response::text('Scorecard created successfully');
        }

        return Response::error("Failed to create scorecard. {$result->errorMessage}");
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase, including directory if any (e.g., "EnumeratedHouseholds", "Households/AverageInterviewTime")'),
            'title' => $schema->string()->description('Human-readable title'),
            'data_source' => $schema->string()->description('Name of the data source this scorecard queries'),
        ];
    }
}
