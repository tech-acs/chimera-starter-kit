<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class DataExport extends Command
{
    protected $signature = 'chimera:data-export
                            {--exclude-table=* : Tables to exclude from the export}';

    protected $description = 'Dump postgres data (from some tables) to file';

    protected array $tables = [
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

    public function handle()
    {
        $pgsqlConfig = config('database.connections.pgsql');
        $dumpFile = base_path() . '/data-export.sql';

        $excludedTables = $this->option('exclude-table');
        if ($excludedTables) {
            $this->tables = array_values(array_filter($this->tables, function($table) use ($excludedTables) {
                return ! in_array($table, $excludedTables);
            }));
        }

        try {
            \Spatie\DbDumper\Databases\PostgreSql::create()
                ->setDbName($pgsqlConfig['database'])
                ->setUserName($pgsqlConfig['username'])
                ->setPassword($pgsqlConfig['password'])
                ->setPort($pgsqlConfig['port'])
                ->includeTables($this->tables)
                ->doNotCreateTables()
                ->addExtraOption('--inserts') // Dump data as INSERT commands (rather than COPY)
                ->addExtraOption('--on-conflict-do-nothing')
                ->addExtraOption('--attribute-inserts') // INSERT commands with explicit column names
                ->dumpToFile($dumpFile);

            info('The postgres data has been dumped to file');
            return self::SUCCESS;
        } catch (\Exception $exception) {
            error('There was a problem dumping the postgres database');
            error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
