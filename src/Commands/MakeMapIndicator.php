<?php

namespace Uneca\Chimera\Commands;

use App\Actions\Maker\CreateMapIndicatorAction;
use Illuminate\Console\Command;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Models\DataSource;
use Spatie\Permission\Models\Permission;
use function Laravel\Prompts\select;
use function Laravel\Prompts\error;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\textarea;

class MakeMapIndicator extends Command
{
    protected $signature = 'chimera:make-map-indicator';
    protected $description = 'Create a new map indicator. Creates file from stub and adds entry in map_indicators table.';

    private function ensureMapIndicatorsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'map_indicators']);
    }

    public function handle(CreateMapIndicatorAction $createMapIndicatorAction)
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }
        $dataSource = select(
            label: "Which data source will this map indicator be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );
        $name = text(
            label: "Map indicator name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            default: DataSource::whereName($dataSource)->first()->title . '/',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:map_indicators,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );
        $title = text(
            label: "Please enter a reader friendly title for the map indicator",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );
        $description = textarea(
            label: "Please enter a description for the map indicator",
            placeholder: "E.g. This map indicator displays on a map by using RAG colors, the percentage of work completed",
            hint: "You can leave this empty for now"
        );
        $this->ensureMapIndicatorsPermissionExists();

        $indicatorAttributes = new MapIndicatorAttributes(
            name: $name,
            title: $title,
            description: $description,
            dataSource: $dataSource,
            stub: resource_path("stubs/map_indicators/default.stub")
        );
        try {
            $createMapIndicatorAction->execute($indicatorAttributes);
            info('Map indicator created successfully.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            error($e->getMessage());
            return self::FAILURE;
        }
    }
}
