<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Uneca\Chimera\Services\AreaTree;
use function Laravel\Prompts\info;

class ExportAreas extends Command
{
    protected $signature = 'chimera:export-areas';

    protected $description = 'Export areas to CSV file which is in the importable format';

    private function generateDynamicAreaSql(Collection $levels): string
    {
        // Creates: a1.name->>'en' AS "County_name", a1.code AS "County_code"
        $selectColumns = $levels->map(function ($name, $index) {
            $alias = $index + 1;
            return "a{$alias}.name->>'en' AS \"{$name}_name\", a{$alias}.code AS \"{$name}_code\"";
        })->implode(", ");

        // Creates: LEFT JOIN areas a1 ON a1.path = subpath(a.path, 0, 1)
        $joins = $levels->map(function ($name, $index) {
            $alias = $index + 1;
            return "LEFT JOIN areas a{$alias} ON a{$alias}.path = subpath(a.path, 0, {$alias})";
        })->implode(" ");

        $leafLevel = $levels->count() - 1;

        return "SELECT {$selectColumns} FROM areas a {$joins} WHERE a.level = {$leafLevel} ORDER BY a.path";
    }

    public function handle()
    {
        info('Exporting areas to CSV file');

        $levels = collect((new AreaTree())->hierarchies);

        Storage::makeDirectory('exports');
        $filePath = storage_path('app/areas.csv');

        $file = fopen($filePath, 'w');

        $csvHeaders = $levels->map(fn($name) => ["{$name}_name", "{$name}_code"])->flatten()->toArray();
        fputcsv($file, $csvHeaders);

        $sql = $this->generateDynamicAreaSql($levels);

        $query = DB::cursor($sql);
        foreach ($query as $row) {
            fputcsv($file, (array) $row);
        }

        fclose($file);

        info('The CSV file has been exported to: ' . $filePath);
        return self::SUCCESS;
    }
}
