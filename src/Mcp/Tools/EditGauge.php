<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Tools\Concerns\ForceModelUpdate;
use Uneca\Chimera\Models\Gauge;

#[Description('Update a gauge\'s metadata after creation. Finds the gauge by name and updates only the provided fields.')]
class EditGauge extends Tool
{
    use ForceModelUpdate;

    public function handle(Request $request): Response
    {
        $name = $request->get('name');
        if (empty($name)) {
            return Response::error('The "name" parameter is required');
        }

        $gauge = Gauge::withoutEagerLoads()->where('name', $name)->first();
        if (! $gauge) {
            return Response::error("Gauge '{$name}' not found");
        }

        $update = [];

        if ($request->has('title')) {
            $update['title'] = $request->get('title');
        }

        if ($request->has('subtitle')) {
            $update['subtitle'] = $request->get('subtitle');
        }

        if ($request->has('description')) {
            $update['description'] = $request->get('description');
        }

        if ($request->has('published')) {
            $update['published'] = $request->boolean('published');
        }

        $this->forceUpdate($gauge, $update);

        return Response::text('Gauge updated successfully');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string('Name of the gauge to edit'),
            'title' => $schema->string('New title (optional)')->optional(),
            'subtitle' => $schema->string('New subtitle (optional)')->optional(),
            'description' => $schema->string('New description (optional)')->optional(),
            'published' => $schema->boolean('Published status (optional)')->optional(),
        ];
    }
}
