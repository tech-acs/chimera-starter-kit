<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Tools\Concerns\ForceModelUpdate;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\Gauge;

#[Description('Update a gauge\'s metadata after creation. Finds the gauge by name and updates only the provided fields. If this tool fails, report the error and stop — do not fall back to workarounds.')]
class EditGauge extends Tool
{
    use ForceModelUpdate;
    use RequiresInitializedMcp;

    public function handle(Request $request): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

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

        $this->forceUpdate($gauge, $update);

        return Response::text('Gauge updated successfully');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Name of the gauge to edit'),
            'title' => $schema->string()->description('New title (optional, max ~20 chars before truncation in UI)')->nullable(),
            'subtitle' => $schema->string()->description('New subtitle (optional, max ~20 chars before truncation in UI)')->nullable(),
        ];
    }
}
