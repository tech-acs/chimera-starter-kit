<?php

namespace Uneca\Chimera\Commands;

use App\Actions\Maker\CreateScorecardAction;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Models\DataSource;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;

class MakeScorecard extends Command
{
    protected $signature = 'chimera:make-scorecard';
    protected $description = 'Create a new scorecard component. Creates file from stub and adds entry in scorecards table.';

    private function ensureScorecardsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'scorecards']);
    }

    public function handle(CreateScorecardAction $createScorecardAction)
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $dataSource = select(
            label: "Which data source will this scorecard be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );

        $name = text(
            label: "Scorecard name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            default: DataSource::whereName($dataSource)->first()->title . '/',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:scorecards,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );

        $title = text(
            label: "Please enter a reader friendly title for the scorecard",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );

        $this->ensureScorecardsPermissionExists();

        $scorecardAttributes = new ScorecardAttributes(
            name: $name,
            title: $title,
            dataSource: $dataSource,
            stub: resource_path("stubs/scorecards/default.stub")
        );
        try {
            $createScorecardAction->execute($scorecardAttributes);
            info('Scorecard created successfully.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            error($e->getMessage());
            return self::FAILURE;
        }
    }
}
