<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Gauge;

#[Description('Update an existing gauge\'s metadata. Only provided fields will be updated.')]
class EditGauge extends Tool
{
    public function handle(Request $request): Response
    {
        try {
            $gauge = $this->resolveModel($request);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::error('Database table not found. Migrations may need to be run.');
        }

        if (! $gauge) {
            return Response::error('Gauge not found. Provide either an id or a name.');
        }

        $updates = [];
        if ($request->has('title')) {
            $updates['title'] = $request->string('title');
        }
        if ($request->has('subtitle')) {
            $updates['subtitle'] = $request->string('subtitle');
        }
        if ($request->has('data_source')) {
            $updates['data_source'] = $request->string('data_source');
        }

        $gauge->update($updates);

        return Response::text("Gauge '{$gauge->title}' (ID: {$gauge->id}) updated.");
    }

    private function resolveModel(Request $request): ?Gauge
    {
        if ($request->has('id')) {
            return Gauge::find($request->integer('id'));
        }

        if ($request->has('name')) {
            return Gauge::where('name', $request->string('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the gauge to update (provide either id or name)'),
            'name' => $schema->string()->description('Name of the gauge to update (provide either id or name)'),
            'title' => $schema->string()->description('New title'),
            'subtitle' => $schema->string()->description('New subtitle'),
            'data_source' => $schema->string()->description('New data source name'),
        ];
    }
}
