<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\MapIndicator;

#[Description('Update an existing map indicator\'s metadata. Only provided fields will be updated.')]
class EditMapIndicator extends Tool
{
    public function handle(Request $request): Response
    {
        try {
            $mapIndicator = $this->resolveModel($request);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::error('Database table not found. Migrations may need to be run.');
        }

        if (! $mapIndicator) {
            return Response::error('Map indicator not found. Provide either an id or a name.');
        }

        $updates = [];
        if ($request->has('title')) {
            $updates['title'] = $request->string('title');
        }
        if ($request->has('description')) {
            $updates['description'] = $request->string('description');
        }
        if ($request->has('data_source')) {
            $updates['data_source'] = $request->string('data_source');
        }

        $mapIndicator->update($updates);

        return Response::text("Map indicator '{$mapIndicator->title}' (ID: {$mapIndicator->id}) updated.");
    }

    private function resolveModel(Request $request): ?MapIndicator
    {
        if ($request->has('id')) {
            return MapIndicator::find($request->integer('id'));
        }

        if ($request->has('name')) {
            return MapIndicator::where('name', $request->string('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the map indicator to update (provide either id or name)'),
            'name' => $schema->string()->description('Name of the map indicator to update (provide either id or name)'),
            'title' => $schema->string()->description('New title'),
            'description' => $schema->string()->description('New description'),
            'data_source' => $schema->string()->description('New data source name'),
        ];
    }
}
