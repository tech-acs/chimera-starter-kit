<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class DataImport extends Command
{
    protected $signature = 'chimera:data-import {--command} {--do-not-truncate=*}';
    protected $description = 'Restore postgres data (some tables) from file';

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
        $dumpFile = base_path() . '/data-export.sql';

        if ($this->option('command')) {
            $command = "psql --host={$pgsqlConfig['host']} --port={$pgsqlConfig['port']} --username={$pgsqlConfig['username']} --file=\"{$dumpFile}\" {$pgsqlConfig['database']}";
            $this->newLine()->line("You can use the command below to manually import the data using the psql tool (enter password when prompted)");
            $this->info($command);
            $this->newLine();
            return self::SUCCESS;
        }

        if (! file_exists($dumpFile)) {
            $this->newLine()->error('No data-export.sql file found to import');
            $this->newLine();
            return self::FAILURE;
        }

        $doNotTruncate = $this->option('do-not-truncate');
        foreach ($this->tables as $table) {
            if (! in_array($table, $doNotTruncate)) {
                DB::table($table)->truncate();
            }
        }

        (new Process(
                ['psql', "--dbname={$pgsqlConfig['database']}", "--username={$pgsqlConfig['username']}", "--host={$pgsqlConfig['host']}", "--port={$pgsqlConfig['port']}", "--file={$dumpFile}"],
                base_path(),
                ['PGPASSWORD' => "{$pgsqlConfig['password']}"]
            ))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });

        $this->info('Data imported successfully');
        return self::SUCCESS;
    }
}
