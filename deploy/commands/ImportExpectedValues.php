<?php

namespace App\Console\Commands;

use App\Services\AreaTree;
use App\Services\Traits\InteractiveCommand;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportExpectedValues extends Command
{
    protected $signature = 'chimera:import-expected-values
                            {file : CSV or XSLX file that contains the expected values}
                            {--truncate : Whether the expected values table should be truncated before importing the indicator values}
                            {--zero-pad-codes : Whether to zero pad codes, padding length will be requested for the code column}
                            {--connection= : You can provide a connection name if you have different areas per connection}
                            {--non-additive : Whether the expected values should not be summed up for higher level areas}';

    protected $description = 'Import expected values per smallest area (EA?) from an excel or csv file';

    protected string $table = 'expected_values';
    protected ?string $connection = null;

    use InteractiveCommand;

    public function __construct()
    {
        parent::__construct();
    }

    private function checkDependencies(): void
    {
        if (! class_exists(SimpleExcelReader::class)) {
            $this->error('The spatie/simple-excel package is required. Please install it.');
            $this->newLine();
            exit;
        }
    }

    private function nextLevelUp($currentLevel)
    {
        $levels = (new AreaTree($this->connection))->levels()->flip();
        $currentKeyIndex = $levels->search($currentLevel);
        return $levels[$currentKeyIndex - 1] ?? null;
    }

    private function writeHigherLevelValues(string $indicator, ?string $currentLevel, string $additive = 'y')
    {
        while ($nextLevelUp = $this->nextLevelUp($currentLevel)) {
            $this->info("Now calculating $nextLevelUp level expected values...");

            $propagation = $additive === 'y' ? 'SUM(expected_values.value) AS value' : 'AVG(expected_values.value) AS value';
            $evsUp = DB::table('expected_values')
                ->join('areas', 'areas.code', '=', 'expected_values.area_code')
                ->select('areas.parent_code AS code', DB::raw($propagation))
                ->where('expected_values.indicator', 'ILIKE', $indicator)
                ->where('expected_values.area_type', $currentLevel)
                ->groupBy('areas.parent_code')
                ->get()
                ->map(function ($row) use ($nextLevelUp, $indicator){
                    return [
                        'area_code' => $row->code,
                        'area_type' => $nextLevelUp,
                        'indicator' => $indicator,
                        'value' => $row->value,
                        'connection_name' => $this->connection,
                        'created_at' => Carbon::now(),
                    ];
                });

            $inserted = 0;
            $evsUp->chunk(500)->each(function ($chunk) use (&$inserted) {
                $inserted += DB::table($this->table)->insertOrIgnore($chunk->all());
            });

            $this->info("$inserted values written.");
            $this->newLine();

            $currentLevel = $nextLevelUp;
        }
    }

    public function handle()
    {
        $this->checkDependencies();

        $shouldZeroPad = $this->option('zero-pad-codes');
        $this->connection = $this->option('connection');

        if ($this->option('truncate')) {
            DB::table($this->table)->truncate();
        }

        $file = $this->argument('file');
        try {
            $columns = SimpleExcelReader::create($file)->getHeaders();
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            return 1;
        }
        $menu = array_combine(range(1, count($columns)), array_values($columns));

        $this->newLine();
        $this->info('This command will import indicator expected values.');
        $this->info('Values will be saved as decimal values (to 2 decimal places) along side column names given in your file.');

        $levels = (new AreaTree($this->connection))->levels();
        $lowestLevel = $levels->search($levels->max());
        $areaCodeColumn = $this->choice("Which column holds the {$lowestLevel} codes directly associated to the expected values?", $menu);
        $zeroPadLength = 0;
        if ($shouldZeroPad) {
            $zeroPadLength = $this->askValid(
                "Enter the target length to zero pad codes to",
                'length',
                ['required', 'numeric', 'max:20']
            );
        }

        $currentLevel = $lowestLevel;
        do {
            $indicatorColumn = $this->choice("Which column holds the expected values for the indicator you want to import?", $menu);
            $additive = $this->choice("Are the values additive? (Should higher area level values be sums of lower ones?)", ['y' => 'Yes', 'n' => 'No'], 'y');
            $rows = SimpleExcelReader::create($file)->getRows();

            $expectedValues = $rows
                ->unique(function ($row) use ($areaCodeColumn) {
                    return $row[$areaCodeColumn];
                })
                ->map(function ($item) use ($currentLevel, $indicatorColumn, $areaCodeColumn, $zeroPadLength) {
                    return [
                        'indicator' => $indicatorColumn,
                        'area_code' => Str::padLeft($item[$areaCodeColumn], (int)$zeroPadLength, '0'),
                        'area_type' => $currentLevel,
                        'value' => $item[$indicatorColumn],
                        'connection_name' => $this->connection,
                        'created_at' => Carbon::now(),
                    ];
                })
                ->values();

            $inserted = 0;
            $expectedValues->chunk(500)->each(function ($chunk) use (&$inserted) {
                $inserted += DB::table($this->table)->insertOrIgnore($chunk->all());
            });

            $this->info("$inserted expected values have been imported for $indicatorColumn indicator at $currentLevel level.");
            $this->newLine();

            $this->writeHigherLevelValues($indicatorColumn, $currentLevel, $additive);

            $stop = $this->choice(
                "Have you finished importing expected values?",
                ['y' => 'Yes', 'n' => "No, I have more to import!"]
            );
        } while ($stop === 'n');
        return 0;
    }
}
