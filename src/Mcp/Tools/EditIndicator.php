<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Enums\IndicatorScope;
use Uneca\Chimera\Mcp\Tools\Concerns\ForceModelUpdate;
use Uneca\Chimera\Models\Indicator;

#[Description('Update an indicator\'s metadata after creation. For Plotly traces and layout, use EditChart instead. Finds the indicator by name and updates only the provided fields. If this tool fails, report the error and stop — do not fall back to workarounds.')]
class EditIndicator extends Tool
{
    use ForceModelUpdate;

    public function handle(Request $request): Response
    {
        $name = $request->get('name');
        if (empty($name)) {
            return Response::error('The "name" parameter is required');
        }

        $indicator = Indicator::withoutEagerLoads()->where('name', $name)->first();
        if (! $indicator) {
            return Response::error("Indicator '{$name}' not found");
        }

        $update = [];

        if ($request->has('title')) {
            $update['title'] = $request->get('title');
        }

        if ($request->has('description')) {
            $update['description'] = $request->get('description');
        }

        if ($request->has('help')) {
            $update['help'] = $request->get('help');
        }

        if ($request->has('data')) {
            $update['data'] = $request->get('data');
        }

        if ($request->has('layout')) {
            $update['layout'] = $request->get('layout');
        }

        if ($request->has('published')) {
            $update['published'] = $request->boolean('published');
        }

        if ($request->has('scope')) {
            $scope = $request->get('scope');
            $validScopes = array_column(IndicatorScope::cases(), 'value');
            if (! in_array($scope, $validScopes)) {
                return Response::error("Invalid scope '{$scope}'. Valid values: ".implode(', ', $validScopes));
            }
            $update['scope'] = $scope;
        }

        $this->forceUpdate($indicator, $update);

        return Response::text('Indicator updated successfully');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Name of the indicator to edit'),
            'title' => $schema->string()->description('New title (optional)')->nullable(),
            'description' => $schema->string()->description('New description (optional)')->nullable(),
            'help' => $schema->string()->description('New help text (optional)')->nullable(),
            'data' => $schema->array()->nullable(),
            'layout' => $schema->object()->nullable(),
            'published' => $schema->boolean()->description('Published status (optional)')->nullable(),
            'scope' => $schema->string()->description("Scope: 'Pages only', 'Area insights only', or 'Everywhere' (optional)")->nullable(),
        ];
    }
}
