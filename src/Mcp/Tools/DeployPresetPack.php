<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Actions\Maker\CreateArtefactAction;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Mcp\Services\PresetPackService;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Models\Scorecard;

#[Description('Deploy artefacts from a preset pack. Specify the pack name, which data source to use, and optionally which artefacts to include (omit to deploy all). The tool creates each artefact via the same action used by the individual create tools. After deployment call validate-artefact on each created artefact.')]
class DeployPresetPack extends Tool
{
    use RequiresInitializedMcp;

    private const TYPE_CONFIG = [
        'scorecard' => [
            'model' => Scorecard::class,
            'namespace' => '\Livewire\Scorecard',
            'stub' => 'stubs/scorecards/default.stub',
        ],
        'indicator' => [
            'model' => Indicator::class,
            'namespace' => '\Livewire\Indicator',
            'stub' => 'stubs/indicators/default.stub',
        ],
        'gauge' => [
            'model' => Gauge::class,
            'namespace' => '\Livewire\Gauge',
            'stub' => 'stubs/gauges/default.stub',
        ],
        'map-indicator' => [
            'model' => MapIndicator::class,
            'namespace' => '\Livewire\MapIndicator',
            'stub' => 'stubs/map_indicators/default.stub',
        ],
        'report' => [
            'model' => Report::class,
            'namespace' => '\Reports',
            'stub' => 'stubs/reports/default.stub',
        ],
    ];

    public function handle(Request $request, CreateArtefactAction $createArtefactAction): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

        $pack = $request->get('pack');
        $dataSourceName = $request->get('data_source');
        $include = $request->get('include');

        if (empty($pack)) {
            return Response::error("The 'pack' parameter is required.");
        }

        if (empty($dataSourceName)) {
            return Response::error("The 'data_source' parameter is required.");
        }

        $dataSource = DataSource::where('name', $dataSourceName)->first();

        if (is_null($dataSource)) {
            $available = DataSource::pluck('name')->implode(', ');

            return Response::error("Data source '{$dataSourceName}' not found. Available: {$available}");
        }

        $service = app(PresetPackService::class);
        $artefacts = $service->getPackArtefacts($pack);

        if (empty($artefacts)) {
            return Response::error("Pack '{$pack}' not found or contains no artefacts.");
        }

        if (! empty($include)) {
            $includeNames = is_array($include) ? $include : [$include];
            $artefacts = array_filter($artefacts, fn ($a) => in_array($a['name'], $includeNames));
        }

        $results = [];
        $dsTitle = preg_replace('/\s+/', '', $dataSource->title);

        foreach ($artefacts as $artefact) {
            $type = $artefact['type'];
            $name = $artefact['name'];
            $title = $artefact['title'];

            if (! isset(self::TYPE_CONFIG[$type])) {
                $results[] = [
                    'name' => $name,
                    'type' => $type,
                    'status' => 'failed',
                    'error' => "Unknown artefact type: {$type}",
                ];
                continue;
            }

            $config = self::TYPE_CONFIG[$type];

            try {
                $prefixedName = $dsTitle . '/' . $name;
                $dto = $this->buildDto($type, $prefixedName, $title, $dataSourceName, $artefact['description'] ?? null);

                if ($dto === null) {
                    $results[] = [
                        'name' => $name,
                        'type' => $type,
                        'status' => 'failed',
                        'error' => "Unsupported type for DTO construction: {$type}",
                    ];
                    continue;
                }

                $result = $createArtefactAction->execute(
                    modelClass: $config['model'],
                    baseNamespace: $config['namespace'],
                    attributes: $dto,
                );

                if ($result->success) {
                    $results[] = [
                        'name' => $name,
                        'type' => $type,
                        'status' => 'success',
                        'filePath' => $result->filePath,
                        'fullName' => $result->artefact->name,
                    ];
                } else {
                    $results[] = [
                        'name' => $name,
                        'type' => $type,
                        'status' => 'failed',
                        'error' => $result->errorMessage,
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'name' => $name,
                    'type' => $type,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $lines = ["## Deployment Report for \"{$pack}\" pack (DS: {$dataSourceName})\n"];

        $successCount = 0;
        $failCount = 0;

        foreach ($results as $r) {
            if ($r['status'] === 'success') {
                $lines[] = "- ✅ **{$r['name']}** ({$r['type']}) → {$r['filePath']}";
                $successCount++;
            } else {
                $lines[] = "- ❌ **{$r['name']}** ({$r['type']}) — {$r['error']}";
                $failCount++;
            }
        }

        $lines[] = '';
        $lines[] = "---";
        $lines[] = "{$successCount} deployed, {$failCount} failed.";

        return Response::text(implode("\n", $lines));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'pack' => $schema->string()
                ->description('Name of the preset pack to deploy (e.g. "census-enumeration")')
                ->required(),

            'data_source' => $schema->string()
                ->description('Name of the data source to use for all artefacts in this pack (use the `name` field from get-data-sources)')
                ->required(),

            'include' => $schema->array()
                ->description('Optional list of artefact names to deploy. Omit to deploy all artefacts in the pack.')
                ->items($schema->string()),
        ];
    }

    private function buildDto(string $type, string $name, string $title, string $dataSource, ?string $description): ?object
    {
        return match ($type) {
            'scorecard' => new ScorecardAttributes(
                name: $name,
                title: $title,
                dataSource: $dataSource,
                stub: resource_path(self::TYPE_CONFIG['scorecard']['stub']),
            ),
            'indicator' => new IndicatorAttributes(
                name: $name,
                title: $title,
                dataSource: $dataSource,
                type: 'default',
                description: $description,
                data: [],
                layout: [],
                stub: resource_path(self::TYPE_CONFIG['indicator']['stub']),
            ),
            'gauge' => new GaugeAttributes(
                name: $name,
                title: $title,
                subtitle: '',
                dataSource: $dataSource,
                stub: resource_path(self::TYPE_CONFIG['gauge']['stub']),
            ),
            'map-indicator' => new MapIndicatorAttributes(
                name: $name,
                title: $title,
                description: $description,
                dataSource: $dataSource,
                stub: resource_path(self::TYPE_CONFIG['map-indicator']['stub']),
            ),
            'report' => new ReportAttributes(
                name: $name,
                title: $title,
                description: $description,
                dataSource: $dataSource,
                stub: resource_path(self::TYPE_CONFIG['report']['stub']),
            ),
            default => null,
        };
    }
}
