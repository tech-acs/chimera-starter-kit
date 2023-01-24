<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class DataImport extends Command
{
    protected $signature = 'chimera:data-import';

    protected $description = 'Restore postgres data (some tables) from file';

    public function handle()
    {
        $dumpFile = base_path() . '/data-export.sql';
        if (! file_exists($dumpFile)) {
            $this->newLine()->error('No data-export.sql file found to import');
            $this->newLine();
            return 1;
        }
        $pgsqlConfig = config('database.connections.pgsql');
        (new Process(
                ['psql', $pgsqlConfig['database'], "--username={$pgsqlConfig['username']}", "--host={$pgsqlConfig['host']}", "--port={$pgsqlConfig['port']}", "--file={$dumpFile}"],
                base_path(),
                ['PGPASSWORD' => $pgsqlConfig['password']]
            ))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });

        return Command::SUCCESS;
    }
}
