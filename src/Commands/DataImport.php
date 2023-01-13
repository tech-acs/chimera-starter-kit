<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DataImport extends Command
{
    protected $signature = 'chimera:data-import';

    protected $description = 'Import production destined data from file';

    public function handle()
    {
        $dataExportFile = base_path() . '/data-export.sql';
        $fileHandle = fopen($dataExportFile, 'r');
        $insertedRecords = 0;
        if ($fileHandle) {
            while (($line = fgets($fileHandle)) !== false) {
                if (trim($line) && DB::insert($line)) {
                    $insertedRecords++;
                }
            }
            fclose($fileHandle);
        }
        $this->newLine()->info("$insertedRecords records have been imported.");
        $this->newLine();
        return Command::SUCCESS;
    }
}
