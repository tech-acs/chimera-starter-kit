<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateScorecardAction;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\QueryException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\ResolvesStubPath;
use Uneca\Chimera\Models\Scorecard;

#[Description('Create a new scorecard (numeric summary card) artefact. Generates a Livewire component file from a stub and creates the database record. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/Livewire/{Name}.php.')]
class CreateScorecard extends Tool
{
    use ResolvesStubPath;

    public function handle(Request $request): Response
    {
        $name = $request->string('name');
        $title = $request->string('title');
        $dataSource = $request->string('data_source');

        $dto = new ScorecardAttributes(
            name: $name,
            title: $title,
            dataSource: $dataSource,
            stub: $this->resolveStubPath('scorecards/default.stub'),
        );

        try {
            app(CreateScorecardAction::class)->execute($dto);
        } catch (Exception $e) {
            return Response::error("Failed to create scorecard: {$e->getMessage()}");
        }

        try {
            $scorecard = Scorecard::where('name', $name)->firstOrFail();
        } catch (QueryException $e) {
            return Response::error('Failed to retrieve created scorecard. The database table may not exist. Run the package migrations.');
        }

        return Response::text("Scorecard '{$title}' created. ID: {$scorecard->id}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase (e.g., "EnumeratedHouseholds")'),
            'title' => $schema->string()->description('Human-readable title'),
            'data_source' => $schema->string()->description('Name of the data source this scorecard queries'),
        ];
    }
}
