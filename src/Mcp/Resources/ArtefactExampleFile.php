<?php

namespace Uneca\Chimera\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;
use Uneca\Chimera\Mcp\Services\ArtefactExampleService;

#[Description('Read a specific example implementation by type and name')]
#[MimeType('text/x-php')]
class ArtefactExampleFile extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('examples://artefact/{type}/{name}');
    }

    public function handle(Request $request): Response
    {
        $service = app(ArtefactExampleService::class);
        $type = $request->get('type');
        $name = $request->get('name');

        if (! $service->isValidType($type)) {
            return Response::error("Unknown artefact type: \"{$type}\". Available types: " . implode(', ', $service::TYPES));
        }

        $content = $service->getExample($type, $name);

        if ($content === null) {
            return Response::error("Example not found: \"{$name}\" for type \"{$type}\". Available examples: " . implode(', ', $service->getAvailableNames($type)));
        }
        return Response::text($content);
    }
}
