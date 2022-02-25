<?php

namespace App\Console\Commands;

use App\Services\Traits\InteractiveCommand;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportAreas extends Command
{
    protected $signature = 'chimera:import-areas 
                            {file : CSV or XSLX file that contains the names and codes of the areas} 
                            {--truncate : Whether the table should be truncated before importing the areas} 
                            {--zero-pad-codes : Whether to zero pad codes, padding length will be requested for each code column}
                            {--connection= : You can provide a connection name if you have different areas per connection}';

    protected $description = 'Import hierarchical list of areas from an excel or csv file';

    protected string $table = 'areas';
    protected ?string $connection = null;

    use InteractiveCommand;

    public function __construct()
    {
        parent::__construct();
    }

    private function checkDependencies()
    {
        if (! class_exists(SimpleExcelReader::class)) {
            $this->error('The spatie/simple-excel package is required. Please install it.');
            $this->newLine();
            exit;
        }
    }

    public function handle()
    {
        $this->checkDependencies();

        if ($this->option('truncate')) {
            DB::table($this->table)->truncate();
        }
        $shouldZeroPad = $this->option('zero-pad-codes');
        $this->connection = $this->option('connection');

        $file = $this->argument('file');
        try {
            $columns = SimpleExcelReader::create($file)->getHeaders();
        } catch (\Exception $exception) {
            $this->newLine();
            $this->error($exception->getMessage());
            $this->newLine();
            return 1;
        }

        $menu = array_combine(range(1, count($columns)), array_values($columns));

        $levels = $this->askValid(
            'How many area levels do you have?',
            'levels',
            ['required', 'numeric', 'between:1,10']
        );

        for ($level = 1; $level <= $levels; $level++) {
            $rows = SimpleExcelReader::create($file)->getRows();
            $type = $this->askValid(
                "What do you call level $level areas? (Use singular forms, please. E.g. region, province, district, ea etc.)",
                'type',
                ['required', 'string']
            );
            $nameColumn = $this->choice("Which column holds the $type names?", $menu);
            $codeColumn = $this->choice("Which column holds the $type codes?", $menu);
            $zeroPadLength = 0;
            if ($shouldZeroPad) {
                $zeroPadLength = $this->askValid(
                    "Enter the target length to zero pad to",
                    'length',
                    ['required', 'numeric', 'max:20']
                );
            }
            $levelInfo[$level] = ['column' => $codeColumn, 'zero-pad-length' => $zeroPadLength];

            $levelsAreas = $rows
                ->unique(function ($row) use ($nameColumn, $codeColumn) {
                    return $row[$nameColumn].$row[$codeColumn];
                })
                ->map(function ($item) use ($nameColumn, $codeColumn, $type, $level, $levelInfo, $zeroPadLength) {
                    $parentLevel = $levelInfo[$level - 1] ?? null;
                    return [
                        'name' => trim($item[$nameColumn]),
                        'code' => Str::padLeft($item[$codeColumn], (int)$zeroPadLength, '0'),
                        'type' => strtolower($type),
                        'level' => $level,
                        'parent_code' =>
                            $parentLevel ?
                                Str::padLeft(
                                    $item[$parentLevel['column']] ?? null,
                                    $parentLevel['zero-pad-length'] ?? 0,
                                    '0'
                                ) :
                                null,
                        'connection_name' => $this->connection,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                })
                ->values();

            $inserted = 0;
            $levelsAreas->chunk(500)->each(function ($chunk) use (&$inserted) {
                $inserted += DB::table($this->table)->insertOrIgnore($chunk->all());
            });
            $this->info("$inserted " . Str::plural($type) . " have been imported.");
            $this->newLine();
        }
        return 0;
    }
}
