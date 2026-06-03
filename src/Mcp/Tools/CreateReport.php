<?php

namespace Uneca\Chimera\Mcp\Tools;

use App\Actions\Maker\CreateReportAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\Mcp\Tools\Concerns\ResolvesStubPath;
use Uneca\Chimera\Models\Report;

#[Description('Create a new report (Excel export) artefact. Generates a report class file from a stub and creates the database record with optional scheduling.')]
class CreateReport extends Tool
{
    use ResolvesStubPath;

    public function handle(Request $request): Response
    {
        $name = $request->string('name');
        $title = $request->string('title');
        $dataSource = $request->string('data_source');
        $description = $request->string('description', '');
        $enabled = $request->boolean('enabled', false);

        $dto = new ReportAttributes(
            name: $name,
            title: $title,
            description: $description ?: null,
            dataSource: $dataSource,
            stub: $this->resolveStubPath('reports/default.stub'),
        );

        app(CreateReportAction::class)->execute($dto);

        $report = Report::where('name', $name)->firstOrFail();

        if ($enabled) {
            $report->update(['enabled' => true]);
        }

        return Response::text("Report '{$title}' created. ID: {$report->id}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(
                description: 'Component name in CamelCase (e.g., "HouseholdSummary" or "Area/HouseholdDetails")',
            ),
            'title' => $schema->string(
                description: 'Human-readable title',
            ),
            'data_source' => $schema->string(
                description: 'Name of the data source this report queries',
            ),
            'description' => $schema->string(
                description: 'Optional description',
            ),
            'enabled' => $schema->boolean(
                description: 'Whether the report is enabled for scheduled generation',
            ),
        ];
    }
}
