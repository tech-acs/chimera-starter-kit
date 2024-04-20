<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\DataSource;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class DataExport extends Command
{
    protected $signature = 'chimera:data-export
                            {--exclude-table=* : Tables to exclude from the export}';

    protected $description = 'Dump postgres data (from some tables) to file';

    protected array $tables = [
        'area_hierarchies',
        'areas',
        'indicators',
        'indicator_page',
        'map_indicators',
        'pages',
        'permissions', // ???
        'data_sources',
        'reports',
        'reference_values',
        'scorecards',
    ];

    public function handle()
    {
        $pgsqlConfig = config('database.connections.pgsql');
        $tmpFile = base_path() . '/data-export.tmp';
        $dumpFile = base_path() . '/data-export.sql';

        $excludedTables = $this->option('exclude-table');
        if ($excludedTables) {
            $this->tables = array_values(array_filter($this->tables, function($table) use ($excludedTables) {
                return ! in_array($table, $excludedTables);
            }));
        }

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
            ->dumpToFile($tmpFile);

        try {
            $tmpFileHandle = fopen($tmpFile, 'r');
            $dumpFileHandle = fopen($dumpFile, 'w');
            $databasePasswords = DataSource::pluck('password')->all();
            while (($line = fgets($tmpFileHandle)) !== false) {
                if (! empty(trim($line))) {
                    if (str_contains($line, 'INSERT INTO public.data_sources')) {
                        $line = str_replace($databasePasswords, '*****', $line);
                    }
                    fwrite($dumpFileHandle, $line);
                }
            }
            fclose($dumpFileHandle);
            fclose($tmpFileHandle);
            unlink($tmpFile);
            info('The postgres data has been dumped to file');
            return self::SUCCESS;

        } catch (\Exception $exception) {
            error('There was a problem dumping the postgres database');
            error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
