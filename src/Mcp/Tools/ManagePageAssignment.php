<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Page;
use Uneca\Chimera\Models\Report;

#[Description('Attach or detach an artefact (indicator, map_indicator, or report) to/from a page. Use the action parameter to specify "attach" or "detach".')]
class ManagePageAssignment extends Tool
{
    private array $modelMap = [
        'indicator' => Indicator::class,
        'map_indicator' => MapIndicator::class,
        'report' => Report::class,
    ];

    public function handle(Request $request): Response
    {
        $artefactType = $request->get('artefact_type');
        $artefactName = $request->get('artefact_name');
        $pageSlug = $request->get('page_slug');
        $action = $request->get('action', 'attach');

        if (empty($artefactType) || empty($artefactName) || empty($pageSlug)) {
            return Response::error('The parameters artefact_type, artefact_name, and page_slug are required');
        }

        if (! in_array($action, ['attach', 'detach'])) {
            return Response::error("Invalid action '{$action}'. Valid values: attach, detach");
        }

        $modelClass = $this->modelMap[$artefactType] ?? null;
        if (! $modelClass) {
            return Response::error("Invalid artefact_type '{$artefactType}'. Valid types: " . implode(', ', array_keys($this->modelMap)));
        }

        $artefact = $modelClass::withoutEagerLoads()->where('name', $artefactName)->first();
        if (! $artefact) {
            return Response::error(ucfirst($artefactType) . " '{$artefactName}' not found");
        }

        $page = Page::where('slug', $pageSlug)->first();
        if (! $page) {
            return Response::error("Page with slug '{$pageSlug}' not found");
        }

        if ($action === 'attach') {
            $rank = $request->get('rank', 0);
            $artefact->pages()->syncWithoutDetaching([$page->id => ['rank' => $rank]]);

            return Response::text(ucfirst($artefactType) . " '{$artefactName}' attached to page '{$pageSlug}' (rank: {$rank})");
        }

        $artefact->pages()->detach($page->id);

        return Response::text(ucfirst($artefactType) . " '{$artefactName}' detached from page '{$pageSlug}'");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'artefact_type' => $schema->string()->description('Type of artefact: indicator, map_indicator, or report'),
            'artefact_name' => $schema->string()->description('Name of the artefact to assign'),
            'page_slug' => $schema->string()->description('Slug of the page to assign the artefact to'),
            'rank' => $schema->integer()->description('Sort order on the page (optional, default 0)')->nullable(),
            'action' => $schema->string()->description("Action to perform: 'attach' or 'detach' (optional, default 'attach')")->nullable(),
        ];
    }
}
