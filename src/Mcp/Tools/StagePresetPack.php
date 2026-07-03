<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Services\PresetPackService;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;

#[Description('Read all artefact specifications in a preset pack. Returns each artefact\'s name, type, title, description, and implementation hints. Use this to evaluate feasibility against the data model (read via read-dictionary), then present a Feasibility Matrix to the user and ask which artefacts to deploy.')]
class StagePresetPack extends Tool
{
    use RequiresInitializedMcp;

    public function handle(Request $request): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

        $service = app(PresetPackService::class);
        $pack = $request->get('pack');

        if (empty($pack)) {
            return Response::error("The 'pack' parameter is required.");
        }

        $artefacts = $service->getPackArtefacts($pack);

        if (empty($artefacts)) {
            return Response::error("Pack '{$pack}' not found or contains no artefacts. Available packs: " . implode(', ', array_column($service->listPacks(), 'name')));
        }

        $lines = ["## Preset Pack: {$pack}\n"];

        foreach ($artefacts as $artefact) {
            $lines[] = "### {$artefact['name']}";
            $lines[] = "- **Type:** {$artefact['type']}";
            $lines[] = "- **Title:** {$artefact['title']}";
            $lines[] = "- **Description:** {$artefact['description']}";
            $lines[] = "- **File:** {$artefact['file']}";
            $lines[] = '';
            $lines[] = $artefact['body'];
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = "Total: " . count($artefacts) . " artefact" . (count($artefacts) !== 1 ? 's' : '') . " in pack.";

        return Response::text(implode("\n", $lines));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'pack' => $schema->string()
                ->description('Name of the preset pack to stage (e.g. "census-enumeration")')
                ->required(),
        ];
    }
}
