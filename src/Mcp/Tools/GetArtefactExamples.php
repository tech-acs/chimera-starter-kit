<?php

namespace Uneca\Chimera\Mcp\Tools;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Services\ArtefactExampleService;

#[Description('Read example implementations to use as templates when creating artefacts. Call with just "type" to list available examples, or with "type" and "name" to get the full PHP source code of a specific example.')]
class GetArtefactExamples extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $service = app(ArtefactExampleService::class);
        $type = $request->get('type');
        $name = $request->get('name');

        if (! $service->isValidType($type)) {
            return Response::text("Unknown artefact type: \"{$type}\". Available types: " . implode(', ', $service::TYPES));
        }

        if ($name !== null) {
            $content = $service->getExample($type, $name);

            if ($content === null) {
                return Response::text("Example not found: \"{$name}\" for type \"{$type}\". Available examples: " . implode(', ', $service->getAvailableNames($type)));
            }

            return Response::text($content);
        }

        $examples = $service->listExamples($type);

        $lines = ["Available {$type} examples:\n"];
        foreach ($examples as $ex) {
            $lines[] = "  - {$ex['name']}: {$ex['description']}";
        }

        return Response::text(implode("\n", $lines));
    }

    public function schema(\Illuminate\Contracts\JsonSchema\JsonSchema $schema): array
    {
        $types = implode(', ', ArtefactExampleService::TYPES);

        return [
            'type' => $schema->string()
                ->description("Artefact type ({$types})")
                ->required(),

            'name' => $schema->string()
                ->description('Specific example name (omit to list available examples)'),
        ];
    }
}
