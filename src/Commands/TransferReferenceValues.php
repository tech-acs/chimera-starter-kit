<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\ReferenceValue;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;

class TransferReferenceValues extends Command implements PromptsForMissingInput
{
    protected $signature = 'chimera:transfer-reference-values {class}';

    protected $description = 'Transfer (update or create) reference values synthesized from a data source (listing, etc.) to the areas table';

    protected string $dataSource;
    protected string $indicator;
    protected int $level;
    protected bool $isAdditive;

    protected function promptForMissingArgumentsUsing(): array
    {
        $availableSynthesizers = collect(glob(app_path('ReferenceValueSynthesizers') . '/*.php'))
            ->mapWithKeys(function (string $file) {
                return [$file => basename($file, '.php')];
            })->toArray();
        return [
            'class' => fn () => select(
                label: "Please provide a ReferenceValueSynthesizer class to use",
                options: $availableSynthesizers,
            ),
        ];
    }

    private function writeHigherLevelValues(string $indicator, int $level)
    {
        $aggMethod = $this->isAdditive ? 'SUM(reference_values.value) AS value' : 'AVG(reference_values.value) AS value';
        DB::insert("
            INSERT INTO reference_values(path, level, indicator, value, created_at, updated_at)
            SELECT areas.path, nlevel(agg.path) - 1 AS level, agg.indicator, agg.value, '" . now() . "', '" . now() . "'
            FROM (
                SELECT $aggMethod, subpath(areas.path, 0, $level) AS path, reference_values.indicator
                FROM reference_values INNER JOIN areas ON reference_values.path = areas.path
                WHERE reference_values.indicator = '{$indicator}' AND reference_values.level = $level
                GROUP BY indicator, subpath(areas.path, 0, $level)
            ) AS agg INNER JOIN areas ON agg.path = areas.path
        ");
    }

    public function handle()
    {
        $class = $this->argument('class');
        $synthesizer = app()->make($class);
        $this->dataSource = $synthesizer->dataSource;
        $this->indicator = $synthesizer->indicator;
        $this->level = $synthesizer->level;
        $this->isAdditive = $synthesizer->isAdditive;

        $initialCount = ReferenceValue::count();

        // Get paths one level above (as BreakoutQueryBuilder works down) your leaf areas (EAs)
        $areaPaths = Area::ofLevel($this->level - 1)->get('path');
        foreach ($areaPaths as $area) {
            $prospectiveReferenceData = $this->getData($this->dataSource, $area->path);
            if ($prospectiveReferenceData->isNotEmpty()) {
                $refData = $prospectiveReferenceData->map(function ($ref) {
                    return [
                        'path' => $ref->area_path,
                        'indicator' => $this->indicator,
                        'value' => $ref->value,
                        'level' => $this->level
                    ];
                })->all();
                foreach ($refData as $data) {
                    ReferenceValue::updateOrCreate(
                        ['path' => $data['path'], 'indicator' => $data['indicator']],
                        ['value' => $data['value'], 'level' => $data['level']]
                    );
                }
            }
        }

        for ($level = $this->level; $level > 0; $level--) {
            $this->writeHigherLevelValues($this->indicator, $level);
        }

        $finalCount = ReferenceValue::count();
        info(($finalCount - $initialCount) . " reference values have been added/updated");

        return Command::SUCCESS;
    }
}
