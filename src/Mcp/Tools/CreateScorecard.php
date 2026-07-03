<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Actions\Maker\CreateArtefactAction;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Validation\ScorecardValidationRules;

#[Description('Create a new scorecard (numeric summary card) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call get-data-sources first, parse the dictionary with read-dictionary, read examples via get-artefact-examples. After creation: (1) implement getData() using BreakoutQueryBuilder, (2) call guide-breakout-query-builder for API reference, (3) call validate-artefact to test it. Scorecards use getSingleRow() for scalar values.')]
class CreateScorecard extends Tool
{
    use RequiresInitializedMcp;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

        $validator = Validator::make($request->toArray(), ScorecardValidationRules::rules());

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

        $scorecardAttributes = new ScorecardAttributes(
            name: $validated['name'],
            title: $validated['title'],
            dataSource: $validated['data_source'],
            stub: resource_path('stubs/scorecards/default.stub')
        );

        $result = $createArtefactAction->execute(modelClass: Scorecard::class, baseNamespace: '\Livewire\Scorecard', attributes: $scorecardAttributes);

        if ($result->success) {
            return Response::text("Scorecard '{$result->artefact->name}' created successfully at {$result->filePath}. Use this full name (including the data source prefix) for all subsequent tools (validate-artefact, edit-scorecard).");
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
            'name' => $schema->string()->description('Component name in CamelCase without the data source prefix (e.g., "EnumeratedHouseholds", "AverageInterviewTime"). The data source title is automatically prepended as a directory (e.g., "Households/EnumeratedHouseholds"). Do NOT include the data source folder yourself.'),
            'title' => $schema->string()->description('Human-readable title (max ~20 chars before truncation in UI)'),
            'data_source' => $schema->string()->description('Name of the data source this scorecard queries (use the `name` field from get-data-sources, e.g. "households")'),
        ];
    }
}
