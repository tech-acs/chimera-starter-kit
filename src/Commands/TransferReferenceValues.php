<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\ReferenceValue;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;

class TransferReferenceValues extends Command implements PromptsForMissingInput
{
    protected $signature = 'chimera:transfer-reference-values {class}';

    protected $description = 'Transfer (update or create) reference values synthesized from a data source (listing, etc.) to the reference_values table';

    protected function promptForMissingArgumentsUsing(): array
    {
        $availableSynthesizers = collect(glob(app_path('ReferenceValueSynthesizers') . '/*.php'))
            ->mapWithKeys(function (string $file) {
                return [basename($file, '.php') => basename($file, '.php')];
            })->toArray();
        return [
            'class' => fn () => select(
                label: "Please provide a ReferenceValueSynthesizer class to use",
                options: $availableSynthesizers,
                hint: 'If there is nothing listed, then define some ReferenceValueSynthesizer classes.'
            ),
        ];
    }

    private function writeHigherLevelValues(string $indicator, int $level, bool $isAdditive)
    {
        $aggMethod = $isAdditive ? 'SUM(reference_values.value) AS value' : 'AVG(reference_values.value) AS value';
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
        $classArg = $this->argument('class');
        $class = str("\\App\\ReferenceValueSynthesizers\\$classArg")->rtrim('.php')->toString();
        if (! class_exists($class)) {
            error("$class class not found");
            return Command::FAILURE;
        }
        $synthesizer = app($class);

        $initialCount = ReferenceValue::count();
        // Get paths one level above (as BreakoutQueryBuilder works down) your leaf areas (EAs)
        $areaPaths = Area::ofLevel($synthesizer->level - 1)->get('path');
        foreach ($areaPaths as $area) {
            $prospectiveReferenceData = $synthesizer->getData($synthesizer->dataSource, $area->path);
            if ($prospectiveReferenceData->isNotEmpty()) {
                $refData = $prospectiveReferenceData->map(function ($ref) use ($synthesizer) {
                    return [
                        'path' => $ref->area_path,
                        'indicator' => $synthesizer->indicator,
                        'value' => $ref->value,
                        'level' => $synthesizer->level
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

        for ($level = $synthesizer->level; $level > 0; $level--) {
            $this->writeHigherLevelValues($synthesizer->indicator, $level, $synthesizer->isAdditive);
        }

        $finalCount = ReferenceValue::count();
        info(($finalCount - $initialCount) . " reference values have been added/updated");

        return Command::SUCCESS;
    }
}
