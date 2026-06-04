<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Indicator;

#[Description('Update an existing indicator\'s metadata, Plotly traces (data), or layout. Only provided fields will be updated.')]
class EditIndicator extends Tool
{
    public function handle(Request $request): Response
    {
        try {
            $indicator = $this->resolveModel($request);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::error('Database table not found. Migrations may need to be run.');
        }

        if (! $indicator) {
            return Response::error('Indicator not found. Provide either an id or a name.');
        }

        $updates = [];

        if ($request->has('title')) {
            $updates['title'] = $request->string('title');
        }
        if ($request->has('description')) {
            $updates['description'] = $request->string('description');
        }
        if ($request->has('data')) {
            $updates['data'] = $request->array('data');
        }
        if ($request->has('layout')) {
            $updates['layout'] = $request->array('layout');
        }
        if ($request->has('data_source')) {
            $updates['data_source'] = $request->string('data_source');
        }

        $indicator->update($updates);

        return Response::text("Indicator '{$indicator->title}' (ID: {$indicator->id}) updated.");
    }

    private function resolveModel(Request $request): ?Indicator
    {
        if ($request->has('id')) {
            return Indicator::find($request->integer('id'));
        }

        if ($request->has('name')) {
            return Indicator::where('name', $request->string('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the indicator to update (provide either id or name)'),
            'name' => $schema->string()->description('Name of the indicator to update (provide either id or name)'),
            'title' => $schema->string()->description('New title'),
            'description' => $schema->string()->description('New description'),
            'data_source' => $schema->string()->description('New data source name'),
            'data' => $schema->array()->description('New Plotly traces array. Each trace: type (bar|line|scatter|pie|histogram|area|box|sunburst), x, y, name, meta.columnNames')->items($schema->object()),
            'layout' => $schema->object()->description('New Plotly layout object (title, xaxis, yaxis, legend, margin, colorway, etc.)'),
        ];
    }
}
