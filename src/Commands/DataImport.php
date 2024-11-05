<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class DataImport extends Command
{
    protected $signature = 'chimera:data-import';
    protected $description = 'Restore postgres data (some tables) from file';

    private function restoreTable(array $pgsqlConfig, bool $truncate, string $sourceFile)
    {
        $table = basename($sourceFile, '.sql');
        if ($truncate) {
            DB::table($table)->truncate();
        }
        info("Restoring $table");
        (new Process(
            ['psql', "--dbname={$pgsqlConfig['database']}", "--username={$pgsqlConfig['username']}", "--host={$pgsqlConfig['host']}", "--port={$pgsqlConfig['port']}", "--file={$sourceFile}"],
            base_path(),
            ['PGPASSWORD' => "{$pgsqlConfig['password']}"]
        ))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    public function handle()
    {
        $pgsqlConfig = config('database.connections.pgsql');
        $exportFolder = base_path() . '/data-export';

        if (! file_exists($exportFolder)) {
            error('No data-export directory found');
            return self::FAILURE;
        }

        $files = File::files($exportFolder);
        $importables = collect($files)->map(fn ($file) => $file->getBasename());

        if ($importables->isEmpty()) {
            error('Nothing to import. The "data-export" directory is empty');
            return self::FAILURE;
        }

        $selectedFiles = multiselect(
            label: 'Select the files (tables) you want to restore',
            options: $importables,
            required: "You must select at least one file",
            hint: 'Use the space bar to select and press enter when done.'
        );

        $truncate = confirm(
            label: "Do you want to truncate the tables before restoring the data from the export file?",
            default: true,
            yes: 'Yes',
            no: 'No',
            hint: "Truncating is the safer alternative as it will ensure consistency between dev and prod"
        );

        foreach ($selectedFiles as $file) {
            $this->restoreTable($pgsqlConfig, $truncate, "$exportFolder/$file");
        }

        info('Data import completed');
        return self::SUCCESS;
    }
}
