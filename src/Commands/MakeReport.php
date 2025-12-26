<?php

namespace Uneca\Chimera\Commands;

use App\Actions\Maker\CreateReportAction;
use Illuminate\Console\Command;
use Uneca\Chimera\DTOs\ReportAttributes;
use Uneca\Chimera\Models\DataSource;
use Spatie\Permission\Models\Permission;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\textarea;

class MakeReport extends Command
{
    protected $signature = 'chimera:make-report';
    protected $description = 'Create a new report. Creates file from stub and adds entry in reports table.';

    private function ensureReportsPermissionExists()
    {
        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'reports']);
    }

    public function handle(CreateReportAction $createReportAction)
    {
        $dataSources = DataSource::all();
        if ($dataSources->isEmpty()) {
            error("You have not yet added data sources to your dashboard. Please do so first.");
            return self::FAILURE;
        }

        $dataSource = select(
            label: "Which data source will this report be using?",
            options: $dataSources->pluck('title', 'name')->toArray(),
            hint: "You will not be able to change this later"
        );
        $name = text(
            label: "Report name",
            placeholder: 'E.g. HouseholdsEnumeratedByDay or Household/BirthRate',
            default: DataSource::whereName($dataSource)->first()->title . '/',
            validate: ['name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*$/', 'unique:reports,name']],
            hint: "This will serve as the component name and has to be in camel case"
        );
        $title = text(
            label: "Please enter a reader friendly title for the report",
            placeholder: 'E.g. Households Enumerated by Day or Birth Rate',
            hint: "You can leave this empty for now",
        );
        $description = textarea(
            label: "Please enter a description for the report",
            placeholder: "E.g. This reports generates a file showing a list of all enumerators that have not synced their tablets with the server in the last 24 hours",
            hint: "You can leave this empty for now"
        );
        $this->ensureReportsPermissionExists();

        $reportAttributes = new ReportAttributes(
            name: $name,
            title: $title,
            description: $description,
            dataSource: $dataSource,
            stub: resource_path("stubs/reports/default.stub")
        );
        try {
            $createReportAction->execute($reportAttributes);
            info('Report created successfully.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            error($e->getMessage());
            return self::FAILURE;
        }
    }
}
