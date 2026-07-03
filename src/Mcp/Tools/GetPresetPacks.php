<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Services\PresetPackService;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;

#[Description('List available preset packs and the number of artefacts of each type they contain. Call this first to discover which packs are available for staging.')]
class GetPresetPacks extends Tool
{
    use RequiresInitializedMcp;

    public function handle(Request $request): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

        $service = app(PresetPackService::class);
        $packs = $service->listPacks();

        if (empty($packs)) {
            return Response::text("No preset packs found.\n\nPlace .md artefact files in resources/preset-packs/{pack-name}/{type}/ to create a pack.");
        }

        $lines = ["Available preset packs:\n"];

        foreach ($packs as $pack) {
            $lines[] = "  **{$pack['name']}** — {$pack['title']}";

            if (! empty($pack['description'])) {
                $lines[] = "    {$pack['description']}";
            }

            $total = 0;
            foreach ($pack['artefacts'] as $type => $count) {
                if ($count > 0) {
                    $label = rtrim($type, 's');
                    $lines[] = "    - {$count} {$label}" . ($count > 1 ? 's' : '');
                    $total += $count;
                }
            }

            if ($total === 0) {
                $lines[] = "    (empty)";
            }
        }

        return Response::text(implode("\n", $lines));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
