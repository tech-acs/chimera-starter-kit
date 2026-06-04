<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\Report;

#[Description('Update an existing report\'s metadata and schedule. Only provided fields will be updated.')]
class EditReport extends Tool
{
    public function handle(Request $request): Response
    {
        try {
            $report = $this->resolveModel($request);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::error('Database table not found. Migrations may need to be run.');
        }

        if (! $report) {
            return Response::error('Report not found. Provide either an id or a name.');
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
        if ($request->has('enabled')) {
            $updates['enabled'] = $request->boolean('enabled');
        }

        $report->update($updates);

        return Response::text("Report '{$report->title}' (ID: {$report->id}) updated.");
    }

    private function resolveModel(Request $request): ?Report
    {
        if ($request->has('id')) {
            return Report::find($request->integer('id'));
        }

        if ($request->has('name')) {
            return Report::where('name', $request->string('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the report to update (provide either id or name)'),
            'name' => $schema->string()->description('Name of the report to update (provide either id or name)'),
            'title' => $schema->string()->description('New title'),
            'description' => $schema->string()->description('New description'),
            'data_source' => $schema->string()->description('New data source name'),
            'enabled' => $schema->boolean()->description('Whether the report is enabled'),
        ];
    }
}
