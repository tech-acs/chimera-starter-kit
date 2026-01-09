<?php

namespace Uneca\Chimera\Commands;

use App\Actions\Maker\CreateGaugeAction;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Uneca\Chimera\DTOs\GaugeAttributes;
use Uneca\Chimera\Models\DataSource;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;

class MakeGauge extends Command
{
    protected $signature = 'chimera:make-gauge';
    protected $description = 'Create a new gauge component. Creates file from stub and adds entry in gauges table.';

    private function ensureGaugesPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'gauges']);
    }

    public function handle(CreateGaugeAction $createGaugeAction)
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $dataSource = select(
            label: "Which data source will this gauge be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );

        $name = text(
            label: "Gauge name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            default: DataSource::whereName($dataSource)->first()->title . '/',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:gauges,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );

        $title = text(
            label: "Please enter a reader friendly title for the gauge",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );

        $subtitle = text(
            label: "Please enter a reader friendly sub-title for the gauge",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );

        $this->ensureGaugesPermissionExists();

        $gaugeAttributes = new GaugeAttributes(
            name: $name,
            title: $title,
            subtitle: $subtitle,
            dataSource: $dataSource,
            stub: resource_path("stubs/gauges/default.stub")
        );
        try {
            $createGaugeAction->execute($gaugeAttributes);
            info('Gauge created successfully.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            error($e->getMessage());
            return self::FAILURE;
        }
    }
}
