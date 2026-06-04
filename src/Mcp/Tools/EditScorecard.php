<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Scorecard;

#[Description('Update an existing scorecard\'s metadata. Only provided fields will be updated.')]
class EditScorecard extends Tool
{
    public function handle(Request $request): Response
    {
        try {
            $scorecard = $this->resolveModel($request);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::error('Database table not found. Migrations may need to be run.');
        }

        if (! $scorecard) {
            return Response::error('Scorecard not found. Provide either an id or a name.');
        }

        $updates = [];
        if ($request->has('title')) {
            $updates['title'] = $request->string('title');
        }
        if ($request->has('data_source')) {
            $updates['data_source'] = $request->string('data_source');
        }

        $scorecard->update($updates);

        return Response::text("Scorecard '{$scorecard->title}' (ID: {$scorecard->id}) updated.");
    }

    private function resolveModel(Request $request): ?Scorecard
    {
        if ($request->has('id')) {
            return Scorecard::find($request->integer('id'));
        }

        if ($request->has('name')) {
            return Scorecard::where('name', $request->string('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the scorecard to update (provide either id or name)'),
            'name' => $schema->string()->description('Name of the scorecard to update (provide either id or name)'),
            'title' => $schema->string()->description('New title'),
            'data_source' => $schema->string()->description('New data source name'),
        ];
    }
}
