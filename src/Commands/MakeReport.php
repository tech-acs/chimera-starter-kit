<?php

namespace Uneca\Chimera\Commands;

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Validation\ReportValidationRules;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class MakeReport extends Command
{
    protected $signature = 'chimera:make-report';

    protected $description = 'Create a new report. Creates file from stub and adds entry in reports table.';

    private function ensureReportsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'reports']);
    }

    public function handle(CreateArtefactAction $createArtefactAction)
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error('You have not yet added data sources to your dashboard. Please do so first.');

            return self::FAILURE;
        }

        $dataSource = select(
            label: 'Which data source will this report be using?',
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: 'You will not be able to change this later'
        );
        $name = text(
            label: 'Report name',
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            default: DataSource::whereName($dataSource)->first()->title.'/',
            validate: ['name' => ReportValidationRules::rules()['name']],
            hint: 'This will serve as the component name and has to be in camel case'
        );
        $title = text(
            label: 'Please enter a reader friendly title for the report',
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: 'You can leave this empty for now',
        );
        $description = textarea(
            label: 'Please enter a description for the report',
            placeholder: 'E.g. This reports generates a file showing a list of all enumerators that have not synced their tablets with the server in the last 24 hours',
            hint: 'You can leave this empty for now'
        );
        $this->ensureReportsPermissionExists();

        $attributes = new ReportAttributes(
            name: $name,
            title: $title,
            description: $description,
            dataSource: $dataSource,
            stub: resource_path('stubs/reports/default.stub')
        );
        $result = $createArtefactAction->execute(modelClass: Report::class, baseNamespace: '\Reports', attributes: $attributes);
        if ($result->success) {
            info('Report created successfully.');

            return self::SUCCESS;
        }

        error($result->errorMessage);

        return self::FAILURE;
    }
}
