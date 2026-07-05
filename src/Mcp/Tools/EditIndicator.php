<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Enums\IndicatorScope;
use Uneca\Chimera\Mcp\Tools\Concerns\ForceModelUpdate;
use Uneca\Chimera\Mcp\Tools\Concerns\RequiresInitializedMcp;
use Uneca\Chimera\Models\Indicator;

#[Description('Update an indicator\'s metadata after creation. For Plotly traces and layout, use EditChart instead. Finds the indicator by name and updates only the provided fields. The help field should explain which dictionary records/items the indicator queries and what calculations it performs — populate it automatically after creation using data from read-dictionary. If this tool fails, report the error and stop — do not fall back to workarounds.')]
class EditIndicator extends Tool
{
    use ForceModelUpdate;
    use RequiresInitializedMcp;

    public function handle(Request $request): Response
    {
        if ($abort = $this->abortIfNotInitialized()) {
            return $abort;
        }

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
            'description' => $schema->string()->description('New description (optional, max ~100 chars before truncation in chart-card)')->nullable(),
            'help' => $schema->string()->description('Explanatory text (markdown) for dashboard users about how the indicator sources its data — which dictionary records/items are queried (e.g. POP_REC.P11 for sex) and what calculations or aggregations are applied. Optional.')->nullable(),
            'data' => $schema->array()->nullable(),
            'layout' => $schema->object()->nullable(),
            'scope' => $schema->string()->description("Scope: 'Pages only', 'Area insights only', or 'Everywhere' (optional)")->nullable(),
        ];
    }
}
