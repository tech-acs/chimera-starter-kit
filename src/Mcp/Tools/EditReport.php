<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Mcp\Tools\Concerns\ForceModelUpdate;
use Uneca\Chimera\Models\Report;

#[Description('Update a report\'s metadata after creation. Finds the report by name and updates only the provided fields.')]
class EditReport extends Tool
{
    use ForceModelUpdate;

    public function handle(Request $request): Response
    {
        $name = $request->get('name');
        if (empty($name)) {
            return Response::error('The "name" parameter is required');
        }

        $report = Report::where('name', $name)->first();
        if (! $report) {
            return Response::error("Report '{$name}' not found");
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

        if ($request->has('enabled')) {
            $update['enabled'] = $request->boolean('enabled');
        }

        $this->forceUpdate($report, $update);

        return Response::text('Report updated successfully');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string('Name of the report to edit'),
            'title' => $schema->string('New title (optional)')->optional(),
            'description' => $schema->string('New description (optional)')->optional(),
            'published' => $schema->boolean('Published status (optional)')->optional(),
            'enabled' => $schema->boolean('Enabled status (optional)')->optional(),
        ];
    }
}
