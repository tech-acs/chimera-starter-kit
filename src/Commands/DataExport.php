<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;

class DataExport extends Command
{
    protected $signature = 'chimera:data-export';

    protected $description = 'Dump postgres data (from some tables) to file';

    protected array $tables = [
        'area_hierarchies',
        //'areas',
        //'reference_values',
        'questionnaires',
        'pages',
        'indicators',
        'indicator_page',
        'scorecards',
        'reports',
        'map_indicators',
        //'roles',
        'permissions',
        //'role_has_permissions'
    ];

    public function handle()
    {
        $pgsqlConfig = config('database.connections.pgsql');
        \Spatie\DbDumper\Databases\PostgreSql::create()
            ->setDbName($pgsqlConfig['database'])
            ->setUserName($pgsqlConfig['username'])
            ->setPassword($pgsqlConfig['password'])
            ->includeTables($this->tables)
            ->doNotCreateTables()
            ->addExtraOption('--inserts')
            ->addExtraOption('--on-conflict-do-nothing')
            ->dumpToFile(base_path() . '/data-export.sql');

        $this->newLine()->info('The postgres data has been dumped to file');
        $this->newLine();
        return Command::SUCCESS;
    }
}
