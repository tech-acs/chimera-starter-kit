<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Tools\Concerns\ForceModelUpdate;
use Uneca\Chimera\Models\Scorecard;

#[Description('Update a scorecard\'s metadata after creation. Finds the scorecard by name and updates only the provided fields.')]
class EditScorecard extends Tool
{
    use ForceModelUpdate;

    public function handle(Request $request): Response
    {
        $name = $request->get('name');
        if (empty($name)) {
            return Response::error('The "name" parameter is required');
        }

        $scorecard = Scorecard::withoutEagerLoads()->where('name', $name)->first();
        if (! $scorecard) {
            return Response::error("Scorecard '{$name}' not found");
        }

        $update = [];

        if ($request->has('title')) {
            $update['title'] = $request->get('title');
        }

        if ($request->has('description')) {
            $update['description'] = $request->get('description');
        }

        if ($request->has('published')) {
            $update['published'] = $request->boolean('published');
        }

        if ($request->has('scope')) {
            $update['scope'] = $request->get('scope');
        }

        $this->forceUpdate($scorecard, $update);

        return Response::text('Scorecard updated successfully');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string('Name of the scorecard to edit'),
            'title' => $schema->string('New title (optional)')->optional(),
            'description' => $schema->string('New description (optional)')->optional(),
            'published' => $schema->boolean('Published status (optional)')->optional(),
            'scope' => $schema->string('Scope (optional)')->optional(),
        ];
    }
}
