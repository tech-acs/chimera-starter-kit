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
        $id = $request->integer('id');
        $scorecard = Scorecard::find($id);

        if (! $scorecard) {
            return Response::error("Scorecard with ID {$id} not found.");
        }

        $updates = [];
        if ($request->has('title')) {
            $updates['title'] = $request->string('title');
        }
        if ($request->has('data_source')) {
            $updates['data_source'] = $request->string('data_source');
        }

        $scorecard->update($updates);

        return Response::text("Scorecard '{$scorecard->title}' (ID: {$id}) updated.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer(
                description: 'ID of the scorecard to update',
            ),
            'title' => $schema->string(
                description: 'New title',
            ),
            'data_source' => $schema->string(
                description: 'New data source name',
            ),
        ];
    }
}
