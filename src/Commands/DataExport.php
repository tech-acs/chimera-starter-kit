<?php

namespace Uneca\Chimera\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;

class DataExport extends Command
{
    protected $signature = 'chimera:data-export';

    protected $description = 'Export production destined data to file';

    protected array $tables = [
        'area_hierarchies',
        /*'areas',
        'reference_values',*/
        'questionnaires',
        'pages',
        'indicators',
        'indicator_page',
        'scorecards',
        'reports',
        'map_indicators',
    ];

    protected array $maskedColumns = [
        'questionnaires' => ['username', 'password']
    ];

    const STAMP_FILE = '.data-export-stamp';

    private function readLastExportStamp()
    {
        $stampFile = base_path(self::STAMP_FILE);
        if (file_exists($stampFile)) {
            return Carbon::parse(file_get_contents($stampFile));
        } else {
            return Carbon::now()->subYear();
        }
    }

    private function stampDataExport()
    {
        $stampFile = base_path(self::STAMP_FILE);
        file_put_contents($stampFile, Carbon::now());
    }

    private function getChagnedRows(string $table, Carbon $lastExport)
    {
        return DB::table($table)
            ->select('*')
            ->where('updated_at', '>', $lastExport)
            ->get();
    }

    private function rowsToSql($table, $rows): string
    {
        $sql = [];
        foreach ($rows as $row) {
            $values = array_map(function ($v, $columnName) use ($table) {
                if (in_array($table, array_keys($this->maskedColumns)) && in_array($columnName, $this->maskedColumns[$table])) {
                    return "'******'";
                }
                return "'" . pg_escape_string($v) . "'";
            }, array_values((array)$row), array_keys((array)$row));
            $sql[] = "INSERT INTO $table VALUES(" . implode(',', $values) . ");";
        }
        return implode(PHP_EOL, $sql);
    }

    public function handle()
    {
        $lastExport = $this->readLastExportStamp();
        $outputFile = base_path() . '/data-export.sql';
        $fileHandle = fopen($outputFile, 'w');
        foreach ($this->tables as $table) {
            $result = $this->getChagnedRows($table, $lastExport);
            $sql = $this->rowsToSql($table, $result);
            fwrite($fileHandle, $sql . PHP_EOL);
        }
        fclose($fileHandle);
        $this->stampDataExport();
        return Command::SUCCESS;
    }
}
