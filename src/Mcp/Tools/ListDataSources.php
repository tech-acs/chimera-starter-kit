<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all available data sources (questionnaires) with their IDs, names, active status, and date ranges. Use this tool first to discover which data source to use when creating artefacts.')]
class ListDataSources extends Tool
{
    public function handle(Request $request): Response
    {
        if (! Schema::hasTable('data_sources')) {
            return Response::text('No data sources found. The data_sources table does not exist.');
        }

        $dataSources = DB::table('data_sources')
            ->select(['id', 'name', 'title', 'connection_active', 'start_date', 'end_date'])
            ->orderBy('rank')
            ->orderBy('name')
            ->get();

        if ($dataSources->isEmpty()) {
            return Response::text('No data sources found. The data_sources table is empty.');
        }

        $lines = [];
        $lines[] = 'Available data sources:';
        $lines[] = str_repeat('-', 80);

        foreach ($dataSources as $ds) {
            $title = json_decode($ds->title, true);
            $titleText = is_array($title) ? ($title['en'] ?? $ds->name) : $ds->name;
            $status = $ds->connection_active ? 'active' : 'inactive';
            $dates = '';
            if ($ds->start_date && $ds->end_date) {
                $dates = " ({$ds->start_date} to {$ds->end_date})";
            }
            $lines[] = "  ID: {$ds->id}";
            $lines[] = "  Name: {$ds->name}";
            $lines[] = "  Title: {$titleText}";
            $lines[] = "  Status: {$status}{$dates}";
            $lines[] = '';
        }

        $lines[] = 'When asked to create an artefact, tell the user which data sources are available and ask which one to use.';
        $lines[] = 'Then use the selected data source name when calling create_* tools or parse-dictionary (data_source parameter).';

        return Response::text(implode("\n", $lines));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
