<?php

namespace Uneca\Chimera\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;
use Uneca\Chimera\Mcp\Services\ArtefactExampleService;

#[Description('List available example implementations for a given artefact type')]
class ArtefactExampleIndex extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('examples://artefact/{type}');
    }

    public function handle(Request $request): Response
    {
        $service = app(ArtefactExampleService::class);
        $type = $request->get('type');

        if (! $service->isValidType($type)) {
            return Response::error("Unknown artefact type: \"{$type}\". Available types: " . implode(', ', $service::TYPES));
        }

        $examples = $service->listExamples($type);

        $lines = ["Available {$type} examples:\n"];
        foreach ($examples as $ex) {
            $lines[] = "  - {$ex['name']}: {$ex['description']}";
        }

        return Response::text(implode("\n", $lines));
    }
}
