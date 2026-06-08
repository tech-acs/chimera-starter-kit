<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Models\DataSource;

#[Description('List all available data sources with their IDs, names, active status, and date ranges. Use this tool first to discover which data source to use when creating artefacts.')]
class GetDataSources extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        if (! Schema::hasTable('data_sources')) {
            return Response::text('No data sources found. The data_sources table does not exist.');
        }

        $dataSources = DataSource::active()->orderBy('name')->get();

        if ($dataSources->isEmpty()) {
            return Response::text('No data sources found. The data_sources table is empty.');
        }

        $formattedData = $dataSources->map(function ($dataSource) {
            return [
                'name' => $dataSource->name,
                'title' => $dataSource->title,
                'start_date' => $dataSource->start_date,
                'end_date' => $dataSource->end_date,
            ];
        })->toArray();

        return Response::structured([
            'data' => $formattedData,
        ]);
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'data' => $schema->array()
                ->description('A list of active data sources.')
                ->items(
                    $schema->object()->properties([
                        'name' => $schema->string()
                            ->description('The unique system identifier or slug of the data source.')
                            ->required(),

                        'title' => $schema->string()
                            ->description('The human-readable display title.')
                            ->required(),

                        'start_date' => $schema->string()
                            ->description('The exercise (census, survey, etc.) start date.')
                            ->required(),

                        'end_date' => $schema->string()
                            ->description('The exercise (census, survey, etc.) end date.')
                            ->required(),
                    ])
                ),
        ];
    }
}
