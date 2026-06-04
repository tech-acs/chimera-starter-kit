<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateMapIndicatorAction;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\QueryException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\ResolvesStubPath;
use Uneca\Chimera\Models\MapIndicator;

#[Description('Create a new map indicator (colored map boundaries) artefact. Generates a map indicator class file from a stub and creates the database record. Prerequisites: call list-data-sources first and ask the user which data source to use, then parse the dictionary with parse-dictionary. After creation, implement getData() by editing the file at app/Livewire/{Name}.php.')]
class CreateMapIndicator extends Tool
{
    use ResolvesStubPath;

    public function handle(Request $request): Response
    {
        $name = $request->string('name');
        $title = $request->string('title');
        $dataSource = $request->string('data_source');
        $description = $request->string('description', '');

        $dto = new MapIndicatorAttributes(
            name: $name,
            title: $title,
            description: $description ?: null,
            dataSource: $dataSource,
            stub: $this->resolveStubPath('map_indicators/default.stub'),
        );

        try {
            app(CreateMapIndicatorAction::class)->execute($dto);
        } catch (Exception $e) {
            return Response::error("Failed to create map indicator: {$e->getMessage()}");
        }

        try {
            $mapIndicator = MapIndicator::where('name', $name)->firstOrFail();
        } catch (QueryException $e) {
            return Response::error('Failed to retrieve created map indicator. The database table may not exist. Run the package migrations.');
        }

        return Response::text("Map indicator '{$title}' created. ID: {$mapIndicator->id}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name in CamelCase (e.g., "PopulationDensity" or "Thematic/PovertyIndex")'),
            'title' => $schema->string()->description('Human-readable title'),
            'data_source' => $schema->string()->description('Name of the data source this map indicator queries'),
            'description' => $schema->string()->description('Optional description'),
        ];
    }
}
