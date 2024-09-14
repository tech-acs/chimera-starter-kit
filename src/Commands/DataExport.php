<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;

class DataExport extends Command
{
    protected $signature = 'chimera:data-export';
    protected $description = 'Dump postgres data (from some tables) to file';

    protected array $exportables = [
        'data_sources',
        'area_hierarchies',
        'areas',
        'reference_values',
        'scorecards',
        'pages',
        'indicator_page',
        'indicators',
        'reports',
        'map_indicators',
        'permissions', // ???
    ];

    private function dumpTable(array $pgsqlConfig, string $exportFolder, string $tableName)
    {
        try {
            \Spatie\DbDumper\Databases\PostgreSql::create()
                ->setDbName($pgsqlConfig['database'])
                ->setUserName($pgsqlConfig['username'])
                ->setPassword($pgsqlConfig['password'])
                ->setPort($pgsqlConfig['port'])
                ->includeTables($tableName)
                ->doNotCreateTables()
                ->addExtraOption('--inserts') // Dump data as INSERT commands (rather than COPY)
                ->addExtraOption('--on-conflict-do-nothing')
                ->addExtraOption('--attribute-inserts') // INSERT commands with explicit column names
                ->dumpToFile("$exportFolder/$tableName.sql");
            return true;
        } catch (\Exception $exception) {
            logger('There was a problem dumping the postgres database', ['Exception message:', $exception->getMessage()]);
            return false;
        }
    }

    public function handle()
    {
        $pgsqlConfig = config('database.connections.pgsql');
        $exportFolder = base_path() . '/data-export';
        File::ensureDirectoryExists($exportFolder);

        $selectedTables = multiselect(
            label: 'Select the tables you want to export',
            options: $this->exportables,
            required: "You must select at least one table",
            hint: 'Use the space bar to select and press enter when done.'
        );

        foreach ($selectedTables as $table) {
            $this->dumpTable($pgsqlConfig, $exportFolder, $table);
        }

        info('The selected tables have been dumped to file');
        return self::SUCCESS;
    }
}
