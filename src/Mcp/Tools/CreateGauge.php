<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateGaugeAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\ResolvesStubPath;
use Uneca\Chimera\Models\Gauge;

#[Description('Create a new gauge (visual threshold indicator for Area Insights page) artefact. Generates a Livewire component file from a stub and creates the database record.')]
class CreateGauge extends Tool
{
    use ResolvesStubPath;

    public function handle(Request $request): Response
    {
        $name = $request->string('name');
        $title = $request->string('title');
        $subtitle = $request->string('subtitle', '');
        $dataSource = $request->string('data_source');

        $dto = new GaugeAttributes(
            name: $name,
            title: $title,
            subtitle: $subtitle,
            dataSource: $dataSource,
            stub: $this->resolveStubPath('gauges/default.stub'),
        );

        app(CreateGaugeAction::class)->execute($dto);

        $gauge = Gauge::where('name', $name)->firstOrFail();

        return Response::text("Gauge '{$title}' created. ID: {$gauge->id}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(
                description: 'Component name in CamelCase (e.g., "EnumerationProgress")',
            ),
            'title' => $schema->string(
                description: 'Human-readable title displayed on the gauge',
            ),
            'subtitle' => $schema->string(
                description: 'Optional subtitle displayed below the title',
            ),
            'data_source' => $schema->string(
                description: 'Name of the data source this gauge queries',
            ),
        ];
    }
}
